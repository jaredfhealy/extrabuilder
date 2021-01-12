<?php

/**
 * Build the Transport
 *
 * @package grv
 * @subpackage processors.transport
 */
class GrvBuildTransportProcessor extends modObjectProcessor
{
	public $classKey = 'grvTransport';
	public $languageTopics = array('grv:default');
	public $objectType = 'grv.transport';
	public $logMessages = [];
	public $package, $packageKey, $core, $assets, $builder, $category, $defaultAttributes;

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Get the passed in Transport object or return failure
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		if (!$this->object = $this->modx->getObject($this->classKey, $primaryKey)) {
			return $this->failure('Unable to get the Transport object record');
		}

		// Get the related Package or return failure
		$this->package = $this->modx->getObject('grvPackage', $this->object->get('package'));
		if (!$this->package) {
			return $this->failure('Unable to get the related Package object record.');
		}

		// Package values
		$key = $this->package->get('package_key');
		$this->packageKey = $key;
		$major = $this->object->get('major');
		$minor = $this->object->get('minor');
		$patch = $this->object->get('patch');
		$version = "$major.$minor.$patch";
		$releaseKey = $this->object->get('release');
		$releaseIndex = $this->object->get('release_index');
		$releaseIndex = $releaseIndex !== 0 ? $releaseIndex : '';
		$release = $releaseKey.$releaseIndex;

		// Get the existing namespace
		$namespace = $this->modx->getObject('modNamespace', ['name' => $key]);

		// Calculate the core and assets path values
		$this->core = $this->modx->grv->replaceCorePaths($this->package->get('core_path'), $key);
		$this->assets = $this->modx->grv->replaceCorePaths($this->package->get('assets_path'), $key);

		// Return error if the paths are not correct
		if (!$this->core && !$this->assets) {
			return $this->failure('Either core_path or assets_path is invalid.', [
				'core_path' => $this->package->get('core_path'),
				'assets_path' => $this->package->get('assets_path')
			]);
		}

		// Start the transport
		$this->modx->loadClass('transport.modPackageBuilder','',false, true);
		$this->builder = new modPackageBuilder($this->modx);
		$this->builder->createPackage($key, $version, $release);
		$this->builder->registerNamespace($key, false, true, $namespace->get('path'));

		// Set the default transport attributes
		$this->defaultAttributes = [
			xPDOTransport::UNIQUE_KEY => 'category',
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::RELATED_OBJECTS => false
		];

		// Setup category as main transport mechanism
		$this->category = $this->modx->newObject('modCategory');
		$this->category->set('id', 1);
        $this->category->set('category', $this->object->get('category'));
		$category_attributes = [
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
			xPDOTransport::RELATED_OBJECT_ATTRIBUTES => []
        ];

		// Create main vehicle
		$this->vehicle = $this->builder->createVehicle($this->category, $category_attributes);
		
		// Add menus
		$this->addMenus();
		
		// Add file resolvers
		$this->addFileResolvers();

		// Add resolvers from the build directory
		$this->addBuildResolvers();

		// Add the category vehicle
		$this->builder->putVehicle($this->vehicle);

		// Define the package attributes
		$attr = $this->definePackageAttributes();

		// Add the package attributes
		$this->builder->setPackageAttributes($attr);

		// Pack it up
		if ($this->builder->pack()) {
			// Clear the _dist folder
			$this->modx->grv->rrmdir($this->core.'_dist/');

			// Return success
			return $this->success('Transport built to packages directory');
		}
		else {
			// Return success
			return $this->failure('Encountered error while packaging transport.');
		}
	}

	/**
	 * Add any menus associated to this namespace
	 */
	public function addMenus()
	{
		// Setup attributes
		$attr = [
			xPDOTransport::PRESERVE_KEYS => true,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'text',
		];
		
		// Query for any menus
		$menus = $this->modx->getCollection('modMenu', [
			'namespace' => $this->packageKey
		]);
		if ($menus) {
			foreach ($menus as $menu) {
				$vehicle = $this->builder->createVehicle($menu, $attr);
				$this->builder->putVehicle($vehicle);
				unset($vehicle);
			}
		}
	}

	/**
	 * Add any file resolvers based on the defined directories
	 */
	public function addFileResolvers()
	{
		// Add the assets folder
		$this->vehicle->resolve('file', [
            'source' => $this->assets,
            'target' => "return MODX_ASSETS_PATH . 'components/';",
		]);

		// Due to limitations in xPDOTransport, we will copy only the folders
		// and files we need to a _dist/<package_key> directory and add a resolver
		// from there. The _dist folder should be added to your .gitignore file.
		$dist = $this->core."_dist/{$this->packageKey}/";
		if (!is_dir($dist)) {
			mkdir($dist, 0775, true);
		}
		else {
			// Clear the directory and rebuild it empty
			$this->modx->grv->rrmdir($dist);
			mkdir($dist, 0775, true);
		}

		// Copy all files except our "excludes" into the $dist folder
		$this->modx->grv->copyCore($this->core, $dist);
		$this->vehicle->resolve('file', [
			'source' => $dist,
			'target' => "return MODX_CORE_PATH . 'components/';",
		]);
	}

	/**
	 * Add resolvers from _build/resolvers
	 */
	public function addBuildResolvers()
	{
		// Setup the resolver directory
		$resolverTemplatePath = "{$this->core}_build/resolvers/";
		$resolverPath = "{$this->core}_dist/resolvers/";
		if (!is_dir($resolverPath)) {
			mkdir($resolverPath, 0775, true);
		}
		
		// Loop through the resolver tempates
        $resolvers = scandir($resolverTemplatePath);
        foreach ($resolvers as $resolver) {
            if (in_array($resolver[0], ['_', '.'])) {
                continue;
			}
			
			// Handle possible replacements
			$contents = str_replace(
				'{package_key}',
				$this->packageKey,
				file_get_contents($resolverTemplatePath.$resolver)
			);

			// Create the new file in the _dist folder
			if ($contents) {
				file_put_contents($resolverPath.$resolver, $contents);
			}

			// Add the resolver from the _dist folder
			if ($this->vehicle->resolve('php', ['source' => $resolverPath.$resolver])) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Added resolver ' . preg_replace('#\.php$#', '', $resolver));
			}
		}
	}

	/**
	 * Define package attributes for readme, license, etc
	 */
	public function definePackageAttributes()
	{
		// Determine what should be included dynamically
		return array(
			'license' => file_get_contents($this->core . 'LICENSE'),
			'readme' => file_get_contents($this->core . 'docs/readme.txt'),
			'changelog' => file_get_contents($this->core . 'docs/changelog.txt')
		);
	}
}

return 'GrvBuildTransportProcessor';
