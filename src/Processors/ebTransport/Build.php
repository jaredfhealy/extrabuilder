<?php

namespace ExtraBuilder\Processors\ebTransport;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modX;
use xPDO\Transport\xPDOTransport;

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

	/** @var \MODX\Revolution\Transport\modPackageBuilder $builder */
    protected $builder;

    /** @var xPDOTransport */
    protected $transport;

	/** @var object ExtraBuilder\Model\ebPackage $ebPackage */
	public $package;

	/** @var $ebTransport */
	public $ebTransport;

	/** @var boolean $backupOnly */
	public $backupOnly;

	/**
	 * Main category for package
	 * 
	 * @var object $category 
	 */
	public $category;

	/**
	 * Config for the current package being built
	 * 
	 * @var array $packageConfig
	 */
	public $packageConfig = [];

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
        $this->package = $this->modx->getObject($this->eb->getClass('ebPackage'), $this->ebTransport->get('package'));
        if (!$this->package) {
            return $this->failure('Unable to get the related Package object record.');
        }

		// Get the configuration for this package
		$this->packageConfig = $this->eb->getPackageConfig($this->package);
		if ($this->packageConfig === false) {
			return $this->failure("Failed to get package configuration paths.");
		}

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

        // Return error if the paths are not correct
        if (!is_dir($this->packageConfig['corePath']) && !is_dir($this->packageConfig['assetsPath'])) {
            return $this->failure('Either core_path or assets_path is invalid or doesn\'t exist. <br>You must build the package and schema in Package Builder before using Transport Builder.', [
                'core_path' => $this->packageConfig['corePath'],
                'assets_path' => $this->packageConfig['assetsPath'],
            ]);
        }

		// Get the cacheManager for working with files
		$this->cacheManager = $this->modx->getCacheManager();

		/**
		 * Begin the build process
		 * 
		 */

		// Create the xPDOTransport instance with version, name and directory
		$this->transport = $this->createPackage();
		$signature = $this->transport->signature;

		// Get the namespace object
		$nsObj = $this->modx->getObject($this->eb->getClass('modNamespace'), ['name' => $this->packageConfig['cmpNamespace']]);
		if (!$nsObj) {
			return $this->failure("Unable to get Namespace object, make sure it exists: ".$this->packageConfig['cmpNamespace']);
		}

		// If we're using the core path structure, copy from public assets to core assets prior to building the transport
		if ($this->packageConfig['dirStructureType'] === 'core') {
			// Check if public exists
			if (is_dir($this->packageConfig['publicAssetsPath'])) {
				// Copy from public to core
				$this->cacheManager->copyTree($this->packageConfig['publicAssetsPath'], $this->packageConfig['coreAssetsPath']);
			}
		}

		/**
		 * Register the namespace for this package
		 * 
		 * We're not using the modPackageBuilder->registerNamespace since
		 * it doesn't add much value and is just a wrapper that adds
		 * an autoinstall class feature.
		 * 
		 * We'll use our namespace object to package in our install/update file resolvers.
		 */
		$this->addNamespaceWithResolvers($nsObj);

		// Add the menues
		$this->addMenus();

		// Add any system settings
		$this->addSettings();

		// Define the main category and element attributes
		$this->defineAttributes();

		/**
		 * During uninstall, MODX reverses the vehicle array in the manifest and then
		 * uninstalls each vehicle. The vehicle objects are handled first like file resolvers,
		 * then script resolvers run after.
		 * 
		 * If uninstall resolvers need your object model, the files are gone before it runs
		 * if the resovler is attached to the namespace vehicle. Attaching uninstall resolvers
		 * to the category and adding it last ensures it runs first before files are removed.
		 */
		$this->addCategoryWithElements();

		// Add the package attributes
        $this->builder->setPackageAttributes($this->definePackageAttributes());

		// Pack it up
        if ($this->builder->pack()) {
			// If not a backup only, we should have a _dist directory
			if (!$this->backupOnly) {
				// Delete the "_dist" directory created during our two-step build
				// Backup only creates the elements which doesn't need the _dist folder
				$this->cacheManager->deleteTree($this->packageConfig['distPath'], [
					'deleteTop' => true,
					'skipDirs' => false,
					'extensions' => '' 
				]);

				// If this is v3
				if ($this->eb->isV3) {
					// Make a copy of the package to be v2 compliant
					$this->copyPackageForV2($this->builder->directory . $signature . '/', $this->builder->directory . 'v2/' . $signature . '/');

					// Call the pack function but bypass the write manifest
					$fileName = $this->builder->package->path.'v2/'.$signature.'.transport.zip';
					$this->builder->package->_pack($this->builder->package->xpdo, $fileName, $this->builder->package->path.'v2/', $signature);
				}

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
        $packageName = $this->package->get('display');
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
			$this->builder->directory = $this->packageConfig['buildPath'] . '_packages/';

			// Create and return the package
			return $this->builder->createPackage($this->packageConfig['cmpNamespace'], $version, $release);
        } 
		else {
			// Set the backup directory
			$this->builder->directory = $this->packageConfig['buildPath'] . 'backup/';

			// Create and return the package with alternate version and release
            return $this->builder->createPackage($this->packageConfig['cmpNamespace'], '0.0.0', 'backup');
        }
    }

	/**
	 * Add the namespace and any file resolvers
	 * 
	 * @param object $nsObj The namespace object
	 */
	public function addNamespaceWithResolvers($nsObj)
	{
		// Set the namespace property
		$nsObj->set('id', null);
		$this->builder->{'namespace'} = $nsObj;

		// Default namespace attributes
		$nsAttr = [
			xPDOTransport::UNIQUE_KEY    => 'name',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true
		];

		// Add file and build resolvers if this is NOT a backupOnly
		$resolvers = [];
        if (!$this->backupOnly) {
			// Add file resolvers to the category vehicle
			$resolvers = array_merge($resolvers, $this->getFileResolvers());

            // Add resolvers from the build directory
            // Files in the root /resolvers directory are assumed to be install actions
			$resolvers = array_merge($resolvers, $this->getBuildResolvers('install'));

            if (count($resolvers) > 0) {
                // Add resolver attributes
                $nsAttr = array_merge($nsAttr, [
                    xPDOTransport::RESOLVE_FILES => true,
                    xPDOTransport::RESOLVE_PHP   => true
                ]);
            }
        }

		// Create the vehicle
		$v = $this->builder->createVehicle($nsObj, $nsAttr);
		
		// If we have resolvers
        if (count($resolvers) > 0) {
            // Set the resolvers
            $v->resolvers = $resolvers;
        }

		// Add the vehicle to the package
		$this->builder->putVehicle($v);
		unset($v);
	}

    /**
     * Add any menus associated to this namespace
     */
    public function addMenus()
    {
        // Query for any menus
        if ($menus = $this->modx->getCollection('modMenu', ['namespace' => $this->packageConfig['cmpNamespace']])) {
            foreach ($menus as $menu) {
                // Create the vehicle
                $vehicle = $this->builder->createVehicle($menu, [
					xPDOTransport::PRESERVE_KEYS => true,
					xPDOTransport::UPDATE_OBJECT => true,
					xPDOTransport::UNIQUE_KEY => 'text',
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
			if ($settings = $this->modx->getCollection($fullClass, ['namespace' => $this->packageConfig['cmpNamespace']])) {
				// Loop through the results
				foreach ($settings as $setting) {
					// Create the vehicle
					$vehicle = $this->builder->createVehicle($setting, [
						xPDOTransport::PRESERVE_KEYS => true,
						xPDOTransport::UPDATE_OBJECT => true,
						xPDOTransport::UNIQUE_KEY => 'key',
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

		// If there is an assets folder
        if (is_dir($this->packageConfig['publicAssetsPath'])) {
            // Add the assets folder
            $resolvers[] = [
                'type' => 'file',
                'source' => $this->packageConfig['publicAssetsPath'],
                'target' => "return MODX_ASSETS_PATH . 'components/';",
            ];
        }

		// For flexibility in packaged file contents and to replace string values,
		// we will use a _dist/<package_key> directory and add a resolver
		// from there. The _dist folder should be added to your .gitignore file.
		$dist = $this->packageConfig['distPath'] . "{$this->packageConfig['cmpNamespace']}/";

		// Make sure the directory is deleted or doesn't exist and recreate it
		$this->cacheManager->deleteTree($dist);
		if (!$this->cacheManager->writeTree($dist)) {
			return "Check permissions; unable to create directory: $dist";
		}

		// For ExtraBuilder only, copy specific _build directories
		if ($this->packageConfig['cmpNamespace'] === 'extrabuilder') {
			$this->cacheManager->copyTree($this->packageConfig['corePath'] . '_build/resolvers', $dist . '_build/resolvers');
			$this->cacheManager->copyTree($this->packageConfig['corePath'] . '_build/templates', $dist . '_build/templates');
		}

		// Copy all files except our "excludes" into the $dist folder
		$this->cacheManager->copyTree($this->packageConfig['corePath'], $dist, [
			'copy_exclude_items' => ['.', '..', 'workspace.code-workspace', '.svn','.svn/','.svn\\'],
			'copy_exclude_patterns' => ['/^_.*/', '/^\..*/']
		]);
        
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
        $resolverTemplatePath = "{$this->packageConfig['buildPath']}resolvers/";
        $resolverPath = "{$this->packageConfig['distPath']}resolvers/";
        if ($action === 'uninstall') {
            $resolverTemplatePath .= 'uninstall/';
            $resolverPath .= 'uninstall/';
        }
        if (!is_dir($resolverPath)) {
            if (!$this->cacheManager->writeTree($resolverPath)) {
				return "Check permissions, unable to create directory: $resolverPath";
			}
        }
        if (!is_dir($resolverTemplatePath)) {
            // Create the directory
            if (!$this->cacheManager->writeTree($resolverTemplatePath)) {
				return "Check permissions, unable to create directory: $resolverTemplatePath";
			}
        }

        // Loop through the resolver templates
        $results = scandir($resolverTemplatePath);
        foreach ($results as $result) {
            if (in_array($result[0], ['_', '.', '..']) || is_dir($resolverTemplatePath . $result)) {
                continue;
            }

            // Get classes array
			$classArr2 = [];
            $classArr3 = [];
            $objects = $this->package->getMany('Objects');
            if ($objects) {
                foreach ($objects as $object) {
					// MODX 2 has no class prefix
					$classArr2[] = $object->get('class');

					// MODX 3 needs the full namespaced class
                    $classArr3[] = $this->packageConfig['packageKey'] . '\\' . $object->get('class');
                }
            }

			// Destination file path
			$sourceFilePath = $resolverTemplatePath . $result;
			$filePath = $resolverPath . $result;

            // Handle possible replacements and get source contents
			// If the packageKey has "." we're building a package in 2.x
			$packageKey = $this->packageConfig['phpNamespace'];
			if (strpos($this->packageConfig['packageKey'], '.') !== false) {
				$packageKey = $this->packageConfig['packageKey'];
			}
            $contents = str_replace(
                '{package_key}',
                $packageKey,
                file_get_contents($sourceFilePath)
            );
            $contents = str_replace(
                '$classesPlaceholder3',
                var_export($classArr3, true),
                $contents
            );
			$contents = str_replace(
                '$classesPlaceholder2',
                var_export($classArr2, true),
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

		// If this is not a backup only
		$resolvers = [];
        if (!$this->backupOnly) {
            // Attach uninstall resolvers to the category. Files are removed with the namespace
            // But we need the files to be able to uninstall tables.
            // Add Uninstall resolvers: These execute before file resolvers remove all files
            $resolvers = $this->getBuildResolvers('uninstall');
            if (count($resolvers) > 0) {
                // Set the attributes to resolve php files
				$this->categoryAttr = array_merge($this->categoryAttr, [
					xPDOTransport::RESOLVE_FILES => true,
					xPDOTransport::RESOLVE_PHP => true
				]);
            }
        }

		// Create the vehicle and add it
		$v = $this->builder->createVehicle($tempCategory, $this->categoryAttr);

		// If we have resolvers
		if (count($resolvers) > 0) {
			$v->resolvers = $resolvers;
		}

		// Add the vehicle
		$this->builder->putVehicle($v);
		unset($v, $tempCategory);
    }

    /**
     * Define package attributes for readme, license, etc
     */
    public function definePackageAttributes()
    {
        // Determine what should be included dynamically
		$attr = [];
        if (!$this->backupOnly) {
            $attr = [
                'license' => is_file($this->packageConfig['corePath'] . 'LICENSE') ? file_get_contents($this->packageConfig['corePath'] . 'LICENSE') : "",
                'readme' => is_file($this->packageConfig['corePath'] . 'docs/readme.txt') ? file_get_contents($this->packageConfig['corePath'] . 'docs/readme.txt') : "",
                'changelog' => is_file($this->packageConfig['corePath'] . 'docs/changelog.txt') ? file_get_contents($this->packageConfig['corePath'] . 'docs/changelog.txt') : "",
			];
        }

        return $attr;
    }

	/**
	 * Define attributes array
	 */
	public function defineAttributes()
	{
		// Store short variable names for transport constants
		$uniqueKey = xPDOTransport::UNIQUE_KEY;
		$preserveKey = xPDOTransport::PRESERVE_KEYS;
		$updateKey = xPDOTransport::UPDATE_OBJECT;
		$relObjKey = xPDOTransport::RELATED_OBJECTS;
		$relObjAttrKey = xPDOTransport::RELATED_OBJECT_ATTRIBUTES;
		
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

	/**
	 * Copy the transport package and replace namespace
	 * prefixes to make it compatible with v2.
	 * 
	 * @param string $src The source package directory
	 * @param string $dst The destination directory
	 */
	public function copyPackageForV2($src, $dst)
	{
		// If $src is not a directory, just return
		if (!is_dir($src)) {
			$this->eb->logInfo('copyPackageForV2: Source is not a directory: '.$src);
			return;
		}

		// Remove the destination directory and recreate it
		if (is_dir($dst)) {
			$this->cacheManager->deleteTree($dst, [
				'deleteTop' => true,
				'skipDirs' => false,
				'extensions' => '' 
			]);
		}

		// Copy and replace just the manifest
		$contents = file_get_contents($src.'manifest.php');

		// Replace namespace prefixes
		$contents = $this->replaceContentsV2($contents, $src.'manifest.php');

		// Create the directory and write the file
		if (!is_dir($dst)) {
			$this->cacheManager->writeTree($dst);
		}
		file_put_contents($dst.'manifest.php', $contents);

		// Repoint the source directory to MODX/Revolution/
		$src = $src.'MODX/Revolution/';
		$this->copyCount = 0;

		// Copy and replace recursive
		$this->copyAndReplaceV2($src, $dst);
	}

	/**
	 * Copy all files from source to destination directory
	 * 
	 * Modifies file contents as needed to support v2 compatibility
	 *
	 * @param string $src
	 * @param string $dst
	 * @return void
	 */
	private function copyAndReplaceV2($src, $dst)
	{
		// open the source directory 
		$dir = opendir($src);

		// Loop through the files in source directory 
		while (false !== ($file = readdir($dir)) && $this->copyCount < 2000) {
			$this->copyCount++;
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					// Recursively calling copy function for sub directory  
					$this->copyAndReplaceV2($src . '/' . $file, $dst . '/' . $file);
				} else {
					// Get the file contents
					$contents = file_get_contents($src.'/'.$file);

					// Replace namespace prefixes
					$contents = $this->replaceContentsV2($contents, $src.'/'.$file);

					if (!is_dir($dst)) {
						$this->cacheManager->writeTree($dst);
					}

					// Put the new file contents at the destination
					file_put_contents($dst.'/'.$file, $contents);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Replace file contents during build for v2 compatibility
	 *
	 * @param string $contents
	 * @param string $filePath
	 * @return string Modifiled file contents
	 */
	private function replaceContentsV2($contents, $filePath)
	{
		// If this is a vehicle file
		if (strpos($filePath, '.vehicle') !== false || strpos($filePath, 'manifest.php') !== false) {
			// Replace namespace prefixes
			$contents = str_replace('MODX\\\\Revolution\\\\', '', $contents);
			$contents = str_replace('xPDO\\\\Transport\\\\', '', $contents);

			// Replace path related values
			$contents = str_replace('/MODX\\\\/Revolution\\\\', '', $contents);
			$contents = str_replace('MODX/Revolution/', '', $contents);

			// If this is the manifest
			if (strpos($filePath, 'manifest.php') !== false) {
				// Set vehicle_package
				$contents = str_replace('\'vehicle_package\' => \'\'', '\'vehicle_package\' => \'transport\'', $contents);
			}
			else if (strpos($contents, '<?php return array') == 0 && strpos($contents, 'vehicle_package') === false) {
				// Add vehicle_package in front of vehicle_class
				$contents = str_replace('\'vehicle_class\'', '\'vehicle_package\' => \'transport\', \'vehicle_class\'', $contents);
			}

			// If package attribute is present set it to modx
			$contents = str_replace('\'package\' => \'\'', '\'package\' => \'modx\'', $contents);
		}

		// Return the replaced contents
		return $contents;
	}

	/**
	 * Replace namespaces back to v2 compatible
	 *
	 * @param string $contents
	 * @return string Modified contents
	 */
	public function replaceClassesForV2($contents)
	{
		// Map new namespaced classes to the old classes
		$nsMap = [
			'MODX\\Revolution\\Processors\\Processor' => 'modProcessor',
			'MODX\\Revolution\\Processors\\ModelProcessor' => 'modObjectProcessor',
			'MODX\\Revolution\\Processors\\DriverSpecificProcessor' => 'modDriverSpecificProcessor',
			'MODX\\Revolution\\Processors\\Model\\CreateProcessor' => 'modObjectCreateProcessor',
			'MODX\\Revolution\\Processors\\Model\\DuplicateProcessor' => 'modObjectDuplicateProcessor',
			'MODX\\Revolution\\Processors\\Model\\ExportProcessor' => 'modObjectExportProcessor',
			'MODX\\Revolution\\Processors\\Model\\GetListProcessor' => 'modObjectGetListProcessor',
			'MODX\\Revolution\\Processors\\Model\\GetProcessor' => 'modObjectGetProcessor',
			'MODX\\Revolution\\Processors\\Model\\RemoveProcessor' => 'modObjectRemoveProcessor',
			'MODX\\Revolution\\Processors\\Model\\SoftRemoveProcessor' => 'modObjectSoftRemoveProcessor',
			'MODX\\Revolution\\Processors\\Model\\UpdateProcessor' => 'modObjectUpdateProcessor',
			'MODX\\Revolution\\Model\\' => '',
			'MODX\\Revolution\\' => '',
			'xPDO\\Transport\\' => '',
			'xPDO\\Om\\' => '',
			'ExtraBuilder\\Model\\' => '',
		];
		
		// Loop through the nsMap
		foreach ($nsMap as $new => $old) {
			// Replace namespaces in 'use' statements
			$contents = str_replace($new, $old, $contents);

			// Replace class extends
			if (strpos($new, 'Processors') !== false) {
				$classParts = explode('\\', $new);
				$newShort = end($classParts);
				$contents = str_replace("extends $newShort", "extends $old", $contents);
			}
		}

		// Also replace any blocks wrapped in "v3 only" comments
		$key = '//'.'v3 only';
		$start = strpos($contents, $key);
		if ($start !== false) {
			$end = strpos($contents, $key, $start + strlen($key));
			$contents = substr_replace($contents, '', $start, $end - $start + strlen($key));
		}

		// Return the contents
		return $contents;
	}

	public function getLanguageTopics()
    {
        return $this->languageTopics;
    }
}