<?php

namespace ExtraBuilder\Processors\ebTransport;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modX;

/**
 * Handle all build options
 *
 */
class Build extends Processor
{
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';
    public $logMessages = [];

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb;

	/** @var \xPDO\Transport\xPDOTransport $xPDOTransport */
	public $xPDOTransport;

	/** @var \MODX\Revolution\Transport\modPackageBuilder $builder */
    protected $builder;

    /** @var xPDOTransport */
    protected $package;

	/** @var $ebPackage */
	public $ebPackage;

	/** @var $ebTransport */
	public $ebTransport;

	/** @var boolean $backupOnly */
	public $backupOnly;

	/** @var boolean $namespace */
	public $namespace;

	/** @var boolean $category Main category for package */
	public $category;

    /**
     * Override the process function
     *
     */
    public function process()
    {
        // Return error if we don't have our service class
		if (!$this->modx->eb) {
			return $this->failure('Service Class is not defined. Validate connector.php is correct.');
		}
		else {
			// Store a reference to our service class that was loaded in 'connector.php'
			$this->eb =& $this->modx->eb;
		}

		// Get the transport and package records
		if (!$this->ebTransport = $this->modx->getObject($this->eb->getClass('ebTransport'), $this->getProperty('id', false))) {
            return $this->failure('Unable to get the Transport object record');
        }

        // Get the related Package or return failure
        $this->ebPackage = $this->modx->getObject($this->eb->getClass('ebPackage'), $this->ebTransport->get('package'));
        if (!$this->ebPackage) {
            return $this->failure('Unable to get the related Package object record.');
        }

		// Set the namespace and packageKey
		$this->namespace = $this->packageKey = explode("\\", $this->ebPackage->get('package_key'))[0];

		// Handle v2/3 core class differences due to namespaces
		$this->xPDOTransport = $this->eb->getClass('xPDOTransport');

		// Check for the backup elements property
        $this->backupOnly = $this->getProperty('backup_only', 'false') === 'true';

        // Store the categorySourceId
        $this->category = $this->modx->getObject($this->eb->getClass('modCategory'), ['category' => $this->ebTransport->get('category')]);
        if ($this->category) {
            // Get the top level category id
            $this->sourceCategoryId = $this->category->get('id');
        } else {
            $this->failure("Unable to determine source category from: {$this->ebTransport->get('category')}");
        }

        // Calculate the core and assets path values
        $this->core = $this->eb->replaceCorePaths($this->ebPackage->get('core_path'), $this->packageKey);
        $this->assets = $this->eb->replaceCorePaths($this->ebPackage->get('assets_path'), $this->packageKey);

        // Return error if the paths are not correct
        if (!is_dir($this->core) && !is_dir($this->assets)) {
            return $this->failure('Either core_path or assets_path is invalid or don\'t exist. <br>You must build the package and schema in Package Builder before using Transport Builder.', [
                'core_path' => $this->core,
                'assets_path' => $this->assets,
            ]);
        }

		/**
		 * Begin the build process
		 * 
		 */

		// Create the xPDOTransport instance with version, name and directory
		$this->package = $this->createPackage();

		// Get the namespace object
		$nsObj = $this->modx->getObject($this->eb->getClass('modNamespace'), ['name' => $this->namespace]);
		if (!$nsObj) {
			return $this->failure("Unable to get Namespace object, make sure it exists: ".$this->namespace);
		}

		/**
		 * Register the namespace for this package
		 * 
		 * We're not using the modPackageBuilder->registerNamespace since
		 * it doesn't add much value and is just a wrapper that adds
		 * an autoinstall class feature.
		 * 
		 * We'll use our namespace object to package in our file resolvers.
		 */ 
		// Not using the modPackageBuilder->registerNamespace 
		$this->addNamespaceWithResolvers($nsObj);

		// Add the menues
		$this->addMenus();

		// Add any system settings
		$this->addSettings();

		// Define the main category and element attributes
		$this->defineAttributes();

		// Add main category
		$this->addCategoryWithElements();

		// Add the package attributes
        $this->builder->setPackageAttributes($this->definePackageAttributes());

		// Pack it up
        if ($this->builder->pack()) {
			// If not a backup only, we should have a _dist directory
			if (!$this->backupOnly) {
				// Backup only creates the elements which doesn't need the _dist folder
                $this->eb->rrmdir("{$this->core}_dist/");

				// Return success
				return $this->success('Transport built to packages directory');
            }

			// Return success
			return $this->success('Elements backup created in backup/ directory');
		}
		else {
			// Return failure
            return $this->failure('Encountered error while packaging transport.');
		}
    }

	/**
     * @return \xPDO\Transport\xPDOTransport
     */
    public function createPackage()
    {
		// Package values
        $packageName = $this->ebPackage->get('display');
        $major = $this->ebTransport->get('major');
        $minor = $this->ebTransport->get('minor');
        $patch = $this->ebTransport->get('patch');
        $version = "$major.$minor.$patch";
        $releaseKey = $this->ebTransport->get('release');
        $releaseIndex = $this->ebTransport->get('release_index');
        $releaseIndex = $releaseIndex !== 0 ? $releaseIndex : '';
        $release = $releaseKey . $releaseIndex;

		// If this is not v3, load the class
		if (!$this->eb->isV3) {
			$this->modx->loadClass('transport.modPackageBuilder', '', false, true);
		}

		// Determine the class for v2/v3 and initialize the builder
		$modPackageClass = $this->eb->getClass('modPackageBuilder');
        $this->builder = new $modPackageClass($this->modx);

		// If backupOnly set different directories and naming
        if ($this->backupOnly !== true) {
			// Set the destination directory
			$this->builder->directory = $this->core . '_build/_packages/';

			// Create and return the package
            return $this->builder->createPackage($this->packageKey, $version, $release);
        } 
		else {
			// Set the backup directory
			$this->builder->directory = $this->core . '_build/backup/';

			// Create and return the package with alternate version and release
            return $this->builder->createPackage($this->packageKey, '0.0.0', 'backup');
        }
    }

	/**
	 * Add the namespace and any file resolvers
	 * 
	 * @param array $nsObj The namespace object
	 */
	public function addNamespaceWithResolvers($nsObj)
	{
		// Set the namespace property
		$nsObj->set('id', null);
		$this->builder->{'namespace'} = $nsObj;

		// Add file and build resolvers if this is NOT a backupOnly
		$resolvers = [];
        if (!$this->backupOnly) {
            // Add Uninstall resolvers: These execute before file resolvers remove all files
			$resolvers = array_merge($resolvers, $this->getBuildResolvers('uninstall'));

            // Add file resolvers to the category vehicle
			$resolvers = array_merge($resolvers, $this->getFileResolvers());

            // Add resolvers from the build directory
            // Files in the root /resolvers directory are assumed to be install actions
			$resolvers = array_merge($resolvers, $this->getBuildResolvers('install'));
        }

		// Create the vehicle and register it
		//$this->eb->logInfo("Resolvers: ".print_r($resolvers, true));
		$v = $this->builder->createVehicle($nsObj, [
			$this->xPDOTransport::UNIQUE_KEY    => 'name',
            $this->xPDOTransport::PRESERVE_KEYS => true,
            $this->xPDOTransport::UPDATE_OBJECT => true,
            $this->xPDOTransport::RESOLVE_FILES => true,
            $this->xPDOTransport::RESOLVE_PHP   => true
		]);
		
		// Set the resolvers
		foreach ($resolvers as $resolver) {
			array_push($v->resolvers, $resolver);
		}

		// Put the vehicle
		$this->builder->putVehicle($v);
	}

    /**
     * Add any menus associated to this namespace
     */
    public function addMenus()
    {
        // Query for any menus
        if ($menus = $this->modx->getCollection('modMenu', ['namespace' => $this->namespace])) {
            foreach ($menus as $menu) {
                // Create the vehicle
                $vehicle = $this->builder->createVehicle($menu, [
					$this->xPDOTransport::PRESERVE_KEYS => true,
					$this->xPDOTransport::UPDATE_OBJECT => true,
					$this->xPDOTransport::UNIQUE_KEY => 'text',
				]);

				// Add the vehicle to the package
                $this->builder->putVehicle($vehicle);
                unset($vehicle);
            }
            unset($menus);
        }
    }

	/**
     * Add any settings associated to this namespace
     */
    public function addSettings()
    {
        // Classes to add
		$classes = [
			'modSystemSetting', 'modUserSetting', 'modUserGroupSetting', 'modContextSetting'
		];
		
		// For each class
		foreach ($classes as $class) {
			// Get the full class if v3
			$fullClass = $this->eb->getClass($class);

			// Query for any settings
			if ($settings = $this->modx->getCollection($fullClass, ['namespace' => $this->namespace])) {
				// Loop through the results
				foreach ($settings as $setting) {
					// Create the vehicle
					$vehicle = $this->builder->createVehicle($setting, [
						$this->xPDOTransport::PRESERVE_KEYS => true,
						$this->xPDOTransport::UPDATE_OBJECT => true,
						$this->xPDOTransport::UNIQUE_KEY => 'key',
					]);
	
					// Add the vehicle to the package
					$this->builder->putVehicle($vehicle);
					unset($vehicle);
				}
				unset($settings);
			}
		}
    }

    /**
     * Add any file resolvers based on the defined directories
     */
    public function getFileResolvers()
    {
        // Resolvers array
		$resolvers = [];

		// Add the assets folder
        $resolvers[] = [
			'type' => 'file',
            'source' => $this->assets,
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ];

        // Due to limitations in xPDOTransport, we will copy only the folders
        // and files we need to a _dist/<package_key> directory and add a resolver
        // from there. The _dist folder should be added to your .gitignore file.
        $dist = $this->core . "_dist/{$this->packageKey}/";
        if (!is_dir($dist)) {
            if (!mkdir($dist, 0775, true)) {
				return "Check permissions; unable to create directory: $dist";
			}
        } else {
            // Clear the directory and rebuild it empty
            $this->eb->rrmdir($dist);
            if (!mkdir($dist, 0775, true)) {
				return "Check permissions; unable to create directory: $dist";
			}
        }

        // For ExtraBuilder only, copy specific _build directories
        if ($this->packageKey === 'ExtraBuilder') {
            $this->eb->copydir($this->core . '_build/resolvers', $dist . '_build/resolvers');
			$this->eb->copydir($this->core . '_build/templates', $dist . '_build/templates');
        }

        // Copy all files except our "excludes" into the $dist folder
        $this->eb->copyCore($this->core, $dist);
        $resolvers[] = [
			'type' => 'file',
            'source' => $dist,
            'target' => "return MODX_CORE_PATH . 'components/';",
        ];

		// Return the resolvers array
		return $resolvers;
    }

    /**
     * Add resolvers from _build/resolvers
     */
    public function getBuildResolvers($action)
    {
        // Resolvers to return
		$resolvers = [];

		// Setup the resolver directory
        $resolverTemplatePath = "{$this->core}_build/resolvers/";
        $resolverPath = "{$this->core}_dist/resolvers/";
        if ($action === 'uninstall') {
            $resolverTemplatePath .= 'uninstall/';
            $resolverPath .= 'uninstall/';
        }
        if (!is_dir($resolverPath)) {
            if (!mkdir($resolverPath, 0775, true)) {
				return "Check permissions; unable to create directory: $resolverPath";
			}
        }
        if (!is_dir($resolverTemplatePath)) {
            // Create the directory
            if (!mkdir($resolverTemplatePath, 0775, true)) {
				return "Check permissions; unable to create directory: $resolverTemplatePath";
			}
        }

        // Loop through the resolver templates
        $results = scandir($resolverTemplatePath);
        foreach ($results as $result) {
            if (in_array($result[0], ['_', '.', '..']) || is_dir($resolverTemplatePath . $result)) {
                continue;
            }

            // Get classes array
            $classArr = [];
            $objects = $this->ebPackage->getMany('Objects');
            if ($objects) {
                foreach ($objects as $object) {
                    $classArr[] = $this->eb->getClass($object->get('class'));
                }
            }

			// Destination file path
			$sourceFilePath = $resolverTemplatePath . $result;
			$filePath = $resolverPath . $result;

            // Handle possible replacements and get source contents
            $contents = str_replace(
                '{package_key}',
                $this->packageKey,
                file_get_contents($sourceFilePath)
            );
            $contents = str_replace(
                '$classesPlaceholder',
                var_export($classArr, true),
                $contents
            );

            // Create the new file in the _dist folder
            if ($contents) {
                file_put_contents($filePath, $contents);
            }

            // Add the resolver file from the _dist folder
			$resolvers[] = [
				'type' => 'php',
				'source' => $filePath
			];
        }

		// Return the resolvers array
		return $resolvers;
    }

    public function addCategoryWithElements()
    {
		// Element class map
        $classes = [
            'modTemplate' => [
                'alias' => 'Templates'
            ],
            'modTemplateVar' => [
                'alias' => 'TemplateVars'
            ],
            'modSnippet' => [
                'alias' => 'Snippets'
            ],
            'modChunk' => [
                'alias' => 'Chunks'
            ],
            'modPlugin' => [
                'alias' => 'Plugins'
			],
			'modCategory' => [
				'alias' => 'Children'
			]
        ];
		
		// Setup category as main transport mechanism and copy from existing
        $tempCategory = $this->modx->newObject($this->eb->getClass('modCategory'), [
            'category' => $this->category->get('category'),
        ]);
		
		// Loop through the list of classes to retrieve
        foreach ($classes as $class => $options) {
			// Since we have real objects, use the getMany function on each
			if ($children = $this->category->getMany($options['alias'])) {
				// Array for new child records
				$newChildren = [];

				// If this is modPlugin
				if ($class == 'modPlugin') {
					// Array to store the new plugins
					$newPlugins = [];

					// Loop through the plugins
					foreach ($children as $plugin) {
						// Copy the object
						$newPlugin = $this->modx->newObject(
							$this->eb->getClass($class), 
							$plugin->toArray()
						);
						$newPlugin->set('id', '');
						
						// Get any events
						if ($events = $plugin->getMany('PluginEvents')) {
							// Array to store the new events
							$newEvents = [];

							// Loop through any events
							foreach ($events as $event) {
								// Copy the object
								$newEvent = $this->modx->newObject(
									$this->eb->getClass('modPluginEvent'),
									$event->toArray()
								);
								$newEvent->set('id', '');
								
								//  Get any property sets
								if ($sets = $event->getMany('PropertySets')) {
									// Array to store
									$newSets = [];

									// Loop through the sets
									foreach ($sets as $set) {
										// Copy the object
										$newSet = $this->modx->newObject(
											$this->eb->getClass('modPropertySet'), 
											$set->toArray()
										);
										$newSet->set('id', '');

										// Push to the array
										$newSets[] = $newSet;
									}

									// Add many sets to the parent event
									$newEvent->addMany($sets, 'PropertySets');
								}

								// Push the new event to the array and add many
								$newEvents[] = $newEvent;
							}

							// Add many events to the parent plugin
							$newPlugin->addMany($newEvents, 'PluginEvents');
						}

						// Push the new plugins to the array
						$newPlugins[] = $newPlugin;
					}

					// Add many plugins to the category
					$tempCategory->addMany($newPlugins, $options['alias']);
				}
				else {
					// Loop through the children
					foreach ($children as $child) {
						// Copy the object
						$newObj = $this->modx->newObject(
							$this->eb->getClass($class), 
							$child->toArray()
						);
						$newObj->set('id', '');

						// Push to the array
						$newChildren[] = $newObj;
					}

					// Add many to the category for other classes
					$tempCategory->addMany($newChildren, $options['alias']);
				}
            }
        }

		// Create the vehicle and add it
		$v = $this->builder->createVehicle($tempCategory, $this->categoryAttr);
		$this->builder->putVehicle($v);
		unset($v, $tempCategory);
    }

    /**
     * Define package attributes for readme, license, etc
     */
    public function definePackageAttributes()
    {
        // Determine what should be included dynamically
        if ($this->backupOnly) {
            $attr = [
                'license' => 'Backup',
                'readme' => 'Backup',
                'changelog' => 'Backup',
            ];
        } else {
            $attr = array(
                'license' => is_file($this->core . 'LICENSE') ? file_get_contents($this->core . 'LICENSE') : "",
                'readme' => is_file($this->core . 'docs/readme.txt') ? file_get_contents($this->core . 'docs/readme.txt') : "",
                'changelog' => is_file($this->core . 'docs/changelog.txt') ? file_get_contents($this->core . 'docs/changelog.txt') : "",
            );
        }

        return $attr;
    }

	/**
	 * Define attributes array
	 */
	public function defineAttributes()
	{
		// Store short variable names for transport constants
		$uniqueKey = $this->xPDOTransport::UNIQUE_KEY;
		$preserveKey = $this->xPDOTransport::PRESERVE_KEYS;
		$updateKey = $this->xPDOTransport::UPDATE_OBJECT;
		$relObjKey = $this->xPDOTransport::RELATED_OBJECTS;
		$relObjAttrKey = $this->xPDOTransport::RELATED_OBJECT_ATTRIBUTES;
		
		// Set the default transport attributes
        $defaultElementAttr = [
            $uniqueKey => 'name',
            $preserveKey => false,
            $updateKey => true
        ];

		// Set Category attributes array
		$this->categoryAttr = [
            $uniqueKey => 'category',
            $preserveKey => false,
            $updateKey => true,
            $relObjKey => true,
			$relObjAttrKey => [
				'Snippets' => $defaultElementAttr,
				'Chunks' => $defaultElementAttr,
				'PropertySets' => $defaultElementAttr,
				'TemplateVars' => $defaultElementAttr,
				'Templates' => $defaultElementAttr,
				'Children' => $defaultElementAttr
			]
        ];

		// Override the differences from default
		$this->categoryAttr[$relObjAttrKey]['Templates'][$uniqueKey] = 'templatename';
		$this->categoryAttr[$relObjAttrKey]['Children'][$uniqueKey] = ['parent', 'category'];

		// Set modPlugin child attributes and add it
		$modPlugin = $defaultElementAttr;
		$modPlugin[$relObjKey] = true;
		$modPlugin[$relObjAttrKey]['PluginEvents'] = [
			$uniqueKey => ['pluginid','event'],
			$preserveKey => true,
			$updateKey => true,
			$relObjKey => true,
			$relObjAttrKey => [
				'PropertySets' => $defaultElementAttr
			]
		];
		$this->categoryAttr[$relObjAttrKey]['Plugins'] = $modPlugin;
	}

	public function getLanguageTopics()
    {
        return $this->languageTopics;
    }
}