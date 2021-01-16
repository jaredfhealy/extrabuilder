<?php

/**
 * Build the Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderBuildTransportProcessor extends modObjectProcessor
{
	public $classKey = 'ebTransport';
	public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.transport';
	public $logMessages = [];
	public $package, $packageKey, $core, $assets, $builder, $category, $defaultAttr, $sourceCategoryId;

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
		$this->package = $this->modx->getObject('ebPackage', $this->object->get('package'));
		if (!$this->package) {
			return $this->failure('Unable to get the related Package object record.');
		}

		// Package values
		$key = $this->package->get('package_key');
		$packageName = $this->package->get('display');
		$this->packageKey = $key;
		$major = $this->object->get('major');
		$minor = $this->object->get('minor');
		$patch = $this->object->get('patch');
		$version = "$major.$minor.$patch";
		$releaseKey = $this->object->get('release');
		$releaseIndex = $this->object->get('release_index');
		$releaseIndex = $releaseIndex !== 0 ? $releaseIndex : '';
		$release = $releaseKey.$releaseIndex;

		// Check for the backup elements property
		$backupOnly = $this->getProperty('backup_only', 'false');
		$backupOnly = $backupOnly === 'true';

		// Store the categorySourceId
		$sourceCategory = $this->modx->getObject('modCategory', ['category' => $this->object->get('category')]);
		if ($sourceCategory) {
			// Get the top level category id
			$this->sourceCategoryId = $sourceCategory->get('id');
		}
		else {
			$this->failure("Unable to determine source category from: {$this->object->get('category')}");
		}

		// Calculate the core and assets path values
		$this->core = $this->modx->eb->replaceCorePaths($this->package->get('core_path'), $key);
		$this->assets = $this->modx->eb->replaceCorePaths($this->package->get('assets_path'), $key);

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
		if ($backupOnly !== true)
			$this->builder->createPackage($packageName, $version, $release);
		else
			$this->builder->createPackage($packageName, '0.0.0', 'backup');

		// Register the namespace with the default core path
		$this->builder->registerNamespace($key, false, true, $this->core);

		// Set the default transport attributes
		$defaultElementAttr = [
			xPDOTransport::UNIQUE_KEY => 'name',
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::RELATED_OBJECTS => false
		];
		$this->defaultAttr = [
			'modSnippet' => $defaultElementAttr,
			'modChunk' => $defaultElementAttr,
			'modPlugin' => $defaultElementAttr,
			'modTemplateVar' => $defaultElementAttr,
			'modTemplate' => [
				xPDOTransport::UNIQUE_KEY => 'templatename',
				xPDOTransport::PRESERVE_KEYS => false,
				xPDOTransport::UPDATE_OBJECT => true,
				xPDOTransport::RELATED_OBJECTS => false
			]
		];
		$this->categoryAttr = [
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true
		];

		// Setup category as main transport mechanism
		$this->category = $this->modx->newObject('modCategory', [
			'id' => 1,
			'category' => $this->object->get('category')
		]);
		
		// Store all elements into the _build/elements/ directory
		// so that it can be picked up by version control
		$elementsDir = $this->core."_build/backup/";

		// Setup the _build directory
		if (!is_dir($elementsDir)) {
			mkdir($elementsDir, 0775, true);
		}

		// If there are child categories
		$children = $this->modx->getCollection('modCategory', ['parent' => $this->sourceCategoryId]);
		if ($children) {
			return $this->failure("&nbsp;The transport builder currently only supports a single category.<br/>&nbsp;&nbsp;&nbsp;&nbsp;Please move all elements under the top level category, and remove any children categories.");
		}

		// Add menus
		$this->addMenus();

		// Add all elements related to the category
		$this->addAllElements();

		// Create the top level category
		$this->vehicle = $this->builder->createVehicle($this->category, $this->categoryAttr);
		
		// Add file and build resolvers if this is NOT a backupOnly
		if (!$backupOnly) {
			// Add Uninstall resolvers: These execute before file resolvers remove all files
			$this->addBuildResolvers('uninstall');

			// Add file resolvers to the category vehicle
			$this->addFileResolvers();

			// Add resolvers from the build directory
			// Files in the root /resolvers directory are assumed to be install actions
			$this->addBuildResolvers('install');
		}

		// Add the category vehicle
		$this->builder->putVehicle($this->vehicle);

		// Define the package attributes
		$attr = $this->definePackageAttributes($backupOnly);

		// Add the package attributes
		$this->builder->setPackageAttributes($attr);

		// Pack it up
		if ($this->builder->pack()) {
			// If not backupOnly
			if (!$backupOnly) {
				$this->modx->eb->rrmdir("{$this->core}_dist/");
			}
			else {
				// Copy the packages file to the _build/elements directory
				if (is_dir(MODX_CORE_PATH."packages/{$key}-0.0.0-backup/")) {
					$this->modx->eb->rrmdir("{$this->core}_build/backup/{$key}-0.0.0-backup/");
					$this->modx->eb->copydir(MODX_CORE_PATH."packages/{$key}-0.0.0-backup/", "{$this->core}_build/backup/{$key}-0.0.0-backup/");
					copy(MODX_CORE_PATH."packages/{$key}-0.0.0-backup.transport.zip", "{$this->core}_build/backup/{$key}-0.0.0-backup.transport.zip");
				}
			}

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
		// Default menu attributes
		$attr = [
			xPDOTransport::PRESERVE_KEYS => true,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'text',
		];
		
		// Query for any menus
		if ($menus = $this->modx->getCollection('modMenu', ['namespace' => $this->packageKey])) {
			foreach ($menus as $menu) {
				// Create and add the vehicle
				$vehicle = $this->builder->createVehicle($menu, $attr);
				$this->builder->putVehicle($vehicle);
				unset($vehicle);
			}
			unset($menus);
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
			$this->modx->eb->rrmdir($dist);
			mkdir($dist, 0775, true);
		}

		// For ExtraBuilder only, copy specific _build directories
		if ($this->packageKey === 'extrabuilder') {
			//$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Source: '.$this->core.'_build/resolvers/');
			//$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Adding resolvers to '.$dist.'_build/resolvers/');
			$this->modx->eb->copydir($this->core.'_build/resolvers', $dist.'_build/resolvers');
		}
		
		// Copy all files except our "excludes" into the $dist folder
		$this->modx->eb->copyCore($this->core, $dist);
		$this->vehicle->resolve('file', [
			'source' => $dist,
			'target' => "return MODX_CORE_PATH . 'components/';",
		]);
	}

	/**
	 * Add resolvers from _build/resolvers
	 */
	public function addBuildResolvers($action)
	{
		// Setup the resolver directory
		$resolverTemplatePath = "{$this->core}_build/resolvers/";
		$resolverPath = "{$this->core}_dist/resolvers/";
		if ($action === 'uninstall') {
			$resolverTemplatePath .= 'uninstall/';
			$resolverPath .= 'uninstall/';
		}
		if (!is_dir($resolverPath)) {
			mkdir($resolverPath, 0755, true);
		}
		if (!is_dir($resolverTemplatePath)) {
			// Create the directory
			mkdir($resolverTemplatePath, 0755, true);
		}
		
		// Loop through the resolver templates
        $resolvers = scandir($resolverTemplatePath);
        foreach ($resolvers as $resolver) {
            if (in_array($resolver[0], ['_', '.']) || is_dir($resolverTemplatePath.$resolver)) {
                continue;
			}

			// Get classes array
			$classArr = [];
			$objects = $this->package->getMany('Objects');
			if ($objects) {
				foreach ($objects as $object) {
					$classArr[] = $object->get('class');
				}
			}
			
			// Handle possible replacements
			$contents = str_replace(
				'{package_key}',
				$this->packageKey,
				file_get_contents($resolverTemplatePath.$resolver)
			);
			$contents = str_replace(
				"{classes_array}",
				json_encode($classArr),
				$contents
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

	public function addAllElements()
	{
		// Element class map
		$classes = [
			'modTemplate' => [
				'alias' => 'Templates',
				'addMany' => []
			],
			'modTemplateVar' => [
				'alias' => 'TemplateVars',
				'addMany' => []
			],
			'modSnippet' => [
				'alias' => 'Snippets',
				'addMany' => []
			],
			'modChunk' => [
				'alias' => 'Chunks',
				'addMany' => []
			]
		];
		
		// Loop through the list of classes to retrieve
		foreach ($classes as $class => $options) {
			// Query for child objects
			if ($objects = $this->modx->getCollection($class, ['category' => $this->sourceCategoryId])) {
				// Loop through the objects
				$results = [];
				foreach ($objects as $object) {
					$sourceArr = $object->toArray();
					$sourceArr['id'] = null;
					$newObj = $this->modx->newObject($class, $sourceArr);
					if ($newObj) {
						// Add each object to the array
						$results[] = $newObj;
					}

					// Add to the related object attributes
					$this->categoryAttr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES][$options['alias']] = $this->defaultAttr[$class];
				}

				// Add the elements
				$this->category->addMany($results);
				unset($results);
			}
		}
	}

	/**
	 * Define package attributes for readme, license, etc
	 */
	public function definePackageAttributes($backupOnly)
	{
		// Determine what should be included dynamically
		if ($backupOnly) {
			$attr = [
				'license' => 'Backup',
				'readme' => 'Backup',
				'changelog' => 'Backup'
			];
		}
		else {
			$attr = array(
				'license' => is_file($this->core . 'LICENSE') ? file_get_contents($this->core . 'LICENSE') : "",
				'readme' => is_file($this->core . 'docs/readme.txt') ? file_get_contents($this->core . 'docs/readme.txt') : "",
				'changelog' => is_file($this->core . 'docs/changelog.txt') ? file_get_contents($this->core . 'docs/changelog.txt') : ""
			);
		}

		return $attr;
	}
}

return 'ExtrabuilderBuildTransportProcessor';