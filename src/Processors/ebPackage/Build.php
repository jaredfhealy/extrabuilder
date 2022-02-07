<?php

namespace ExtraBuilder\Processors\ebPackage;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modX;
use DOMDocument;
use SimpleXMLElement;
use PDO;
use Exception;

/**
 * Handle all build options
 *
 */
class Build extends Processor
{
    /** 
	 * Current ebPackage object
	 * 
	 * @var ExtraBuilder\Model\ebPackage $package
	 */
	public $package;
	public $languageTopics = array('extrabuilder:default');
    public $logMessages = [];

	/** @var string $packageKey Schema package attribute value */
	public $packageKey = "";

	/**
	 * 3.0 Specific options for parsing the schema
	 *
	 * @var array $schemaOptions
	 */
	public $schemaOptions = [];

	/**
	 * Config for the current package being built
	 * 
	 * @var array $packageConfig
	 */
	public $packageConfig = [];

    /** 
	 * Deafult to preview only
	 * 
	 *  @var boolean $previewOnly 
	 */
    public $previewOnly = true;

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb; 

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
		
		// Get parameters to determine actions
        $this->schemaOptions['writeSchemaOnly'] = $writeSchemaOnly = $this->getProperty('write_schema') === 'true';
        $this->schemaOptions['buildSkip'] = $buildSkip = $this->getProperty('build_skip') === 'true';
        $this->schemaOptions['buildDelete'] = $buildDelete = $this->getProperty('build_delete') === 'true';
        $this->schemaOptions['buildDeleteAndDrop'] = $buildDeleteAndDrop = $this->getProperty('build_delete_drop') === 'true';

        // Get the object using the passed in primary id
		$packageId = $this->getProperty('id', 0);
		$this->package = $this->modx->getObject($this->eb->getClass('ebPackage'), $packageId);
        if (!$this->package) {
            // Return here
            return $this->failure("Unable to retrieve package with supplied ID: $packageId");
        } else {
            $this->logMessages[] = "Found package by id $packageId. Name: " . $this->package->get('display') . ", Package: " . $this->package->get('package_key');
        }

		// Get the configuration for this package
        $this->packageConfig = $this->eb->getPackageConfig($this->package);
		if ($this->packageConfig === false) {
			return $this->failure("Failed to get package configuration paths.");
		}

		// Get the cacheManager for working with files
		$this->cacheManager = $this->modx->getCacheManager();

        // Set preview to false
        if ($buildSkip || $buildDelete || $buildDeleteAndDrop) {
            $this->previewOnly = false;
        }

        // Handle schema deletion
        if (($buildDelete || $buildDeleteAndDrop) && !$this->eb->isV3) {
            // Delete the schema files
			$this->cacheManager->deleteTree($this->packageConfig['modelPath'.$this->eb->version] . 'mysql/', [
				'deleteTop' => true, 
				'skipDirs' => false, 
				'extensions' => ''
			]);
        }

		// If delete and drop was selected
        if ($buildDeleteAndDrop) {
			// Drop all tables
            $this->dropModelTables();
        }

        // Generate the schema as long as it's not for the package builder
        $schema = $this->generateSchema();

        if ($writeSchemaOnly || !$this->previewOnly) {
			// If write only, log out the config
			if ($writeSchemaOnly) {
				$this->logMessages[] = print_r($this->packageConfig, true);
			}

            // Make sure at least the schema directory exists
			$schemaPathKey = 'schemaPath'.$this->eb->version;
			$schemaDir = $this->packageConfig[$schemaPathKey];
			$this->cacheManager->writeTree($schemaDir);

            // Create the schema file
            if (file_put_contents($this->packageConfig['schemaFilePath'], $schema)) {
                // Written successfully
                $this->logMessages[] = "XML Schema written successfully...";
            } else {
                return $this->failure('Unable to write schema file: ' . $this->packageConfig['schemaFilePath'], []);
            }
        }

        // If not preview only
        if (!$this->previewOnly) {
            // Call the build script
            $this->buildSchema();
        }

        $separator = '' . PHP_EOL;
        return $this->success('', [
            'schema' => $schema,
            'core_path' => $this->packageConfig['corePath'],
            'assets_path' => $this->packageConfig['coreAssetsPath'],
            'messages' => implode($separator, $this->logMessages),
        ]);
    }

    /**
     * Build the schema files, create tables
     *
     * Resources used:
     *  - https://github.com/bezumkin/modExtra (build script)
     *
     */
    public function buildSchema()
    {
        /**
		 * Make the assets directories if they don't exist
		 * 
		 * To allow usage of Git or source control and still build
		 * your package in the "core/components/" directory which
		 * is not web accessible, ExtraBuilder syncronizes from
		 * "MODX_ASSETS_PATH . <mycomponent>/" to the corresponding
		 * directory in "core/components/<mycomponent>/assets/".
		 * 
		 * This sync occurs any time you build and any time you
		 * backup all elements.
		 * 
		 * When building the transport package, the directory in
		 * "core/components/" is used as the source.
		 */

		// If we're using the "core" structure type, use the symlink to map publicAssets to coreAssets
        if ($this->packageConfig['dirStructureType'] == 'core') {
			// If the core path exists but public does not, copy from core to public (assume cloned from git or similar)
			if (is_dir($this->packageConfig['coreAssetsPath'])) {
				// If public doesn't exist yet
                if (!is_dir($this->packageConfig['publicAssetsPath'])) {
                    // Copy from core to public
                    $this->cacheManager->copyTree($this->packageConfig['coreAssetsPath'], $this->packageConfig['publicAssetsPath']);
                }
				else {
					// Both directories already exist:
					// Copy contents from public to non-public
					$this->cacheManager->copyTree($this->packageConfig['publicAssetsPath'], $this->packageConfig['coreAssetsPath']);
				}
			}
			else {
				// First check that the coreAssetsPath exists
				$this->cacheManager->writeTree($this->packageConfig['coreAssetsPath']);

				// Check if public exists
				if (is_dir($this->packageConfig['publicAssetsPath'])) {
                    // Copy from public to core
                    $this->cacheManager->copyTree($this->packageConfig['publicAssetsPath'], $this->packageConfig['coreAssetsPath']);
                }
				else {
					// Create the public directory
					$this->cacheManager->writeTree($this->packageConfig['publicAssetsPath']);
				}
			}
        }

		// Create the namespace first in v3 since it plays a key role
		$this->logMessages[] = "Creating Namespace: {$this->packageConfig['cmpNamespace']}";
        $namespaceObj = $this->modx->getObject($this->eb->getClass('modNamespace'), ['name' => $this->packageConfig['cmpNamespace']]);
        if (!$namespaceObj) {
			// Generate a new namespace using designated paths
			$namespaceObj = $this->modx->newObject($this->eb->getClass('modNamespace'), [ 
                'path' => $this->package->get('core_path') ?: "{core_path}components/{$this->packageConfig['cmpNamespace']}/",
                'assets_path' => $this->package->get('assets_path') ?: "{assets_path}components/{$this->packageConfig['cmpNamespace']}/",
            ]);
			$namespaceObj->set('name', $this->packageConfig['cmpNamespace']);

			// Save the new namespace
            if ($namespaceObj->save()) {
                $this->logMessages[] = "Namespace created successfully {$this->packageConfig['cmpNamespace']}...";
            }
        } else {
            $this->logMessages[] = "Namespace, {$this->packageConfig['cmpNamespace']}, already exists. Use the manager to update.";
        }

        // If include lexicon is set to true
        $lexiconPath = '';
        if ($this->package->get('lexicon') === '1') {
            // Make sure the directory exists
            $lexiconPath = $this->packageConfig['corePath'] . 'lexicon/en/';
            if (!is_dir($lexiconPath)) {
                mkdir($lexiconPath, 0775, true);
            }
			$this->packageConfig['lexiconPath'] = $lexiconPath;

            // If the file does not exist yet.
			$lexiconFilePath = $lexiconPath . 'default.inc.php';
            if (!is_file($lexiconFilePath)) {
				$this->logMessages[] = "Adding default lexicon at: $lexiconFilePath";
                // Copy the default lexicon file
				$lexiconSrcPath = $this->eb->config['corePath'].'_build/templates/lexicon/default.inc.tpl';
				$lexiconContent = file_get_contents($lexiconSrcPath);
				if ($lexiconContent !== false) {
					// Replace values
					$lexiconContent = str_replace('{$namespace}', $this->packageConfig['phpNamespace'], $lexiconContent);

					// Write the destination file
					if (file_put_contents($lexiconFilePath, $lexiconContent) !== false) {
						$this->logMessages[] = "Created default lexicon successfully....";
					}
					else {
						$this->logMessages[] = "Failed to create default lexicon";
					}
				}
				else {
					$this->logMessages[] = "Unable to get lexicon source template from: $lexiconSrcPath";
				}
            }
        }

		// Log out all the calculated paths
		$this->logMessages[] = "Sources: " . print_r($this->packageConfig, true);

        // Get the manager and generator
        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();

		// If this is v3
		$parseSchema = false;
		if ($this->eb->isV3 === true) {
			// Set options for parse
			$parseOptions = [
				"compile" => 0,
				"update" => 0,
				"regenerate" => 0,
				"namespacePrefix" => $this->packageConfig['phpNamespace'].'\\'
			];

			/** 
			 * Override update and regenerate as needed. Descriptions from 
			 * xPDOGenerator->outputClasses()
			 * 
			 * $update     Indicates if existing class files should be updated; 0=no,
			 *             1=update platform classes, 2=update all classes.
			 * $regenerate Indicates if existing class files should be regenerated;
			 *             0=no, 1=regenerate platform classes, 2=regenerate all classes.
			 */
			if ($this->schemaOptions['buildDelete'] === true || $this->schemaOptions['buildDeleteAndDrop'] === true) {
				$parseOptions['update'] = 2;
				$parseOptions['regenerate'] = 2;
			}

			// Parse the schema using v3 options
			$parseSchema = $generator->parseSchema(
				$this->packageConfig['schemaFilePath'], 
				$this->packageConfig['sourcePath'],
				$parseOptions
			);
		}
		else {
			// Check the schema format
			if (is_file($this->packageConfig['schemaFilePath'])) {
				if (strpos($this->packageConfig['packageKey'], '.v2.model') === false) {
					return $this->failure("To prepare for MODX 3.0 compatibility, set your package key format to: <mycomponent>.v2.model");
				}
			}
			$parseSchema = $generator->parseSchema(
				$this->packageConfig['schemaFilePath'], 
				MODX_CORE_PATH.'components/'
			);
		}
		
		// Check the result
		if ($parseSchema) {
			$this->logMessages[] = "Schema processing complete, model files generated";
		}
		else {
			$this->failure("Unable to write model files");
			$this->logMessages[] = "Unable to write model files";
		}	

		// Check for a bootstrap.php file for this new extra
		if (!is_file($this->packageConfig['corePath'].'bootstrap.php') && $this->eb->isV3) {
			// Generate the file and return
			$contents = file_get_contents($this->eb->config['corePath'].'_build/templates/bootstrap.tpl');
			$contents = str_replace('{$namespace}', $this->packageConfig['phpNamespace'], $contents);
			file_put_contents($this->packageConfig['corePath'].'bootstrap.php', $contents);

			// Set variables needed by the bootstrap file
			$modx =& $this->modx;

			// Set namespace as an array so it can be accessed in bootstrap.php on first build
			// Since the file was just created, MODX didn't load it
			$namespace = [
				'name' => $this->packageConfig['cmpNamespace'],
				'path' => $this->packageConfig['corePath'],
				'assets_path' => $this->packageConfig['publicAssetsPath']
			];

			// Include the newly generated bootstrap file to register our classes
			@include $this->packageConfig['corePath'].'bootstrap.php';
		}
		else if (!$this->eb->isV3) {
			// In v2 use add package, expecting packageKey format: <mycomponent>.v2.model
			if (!$this->modx->addPackage($this->package->get('package_key'), MODX_CORE_PATH.'components/')) {
				return $this->failure("Unable to add the package and class files: ".$this->package->get('package_key'));
			}
		}

		// Get the child object records
		$objects = $this->package->getMany('Objects');
		if (!$objects) {
			return $this->failure('Unable to retrieve related objects/tables.');
		}

		// Loop through the tables
        foreach ($objects as $object) {
			// Convert class for MODX 3 if needed
			$className = $object->get('class');
			if ($this->eb->isV3) {
				$className = $this->packageConfig['classPrefix'].$className;
				$this->logMessages[] = "Checking if class exists: $className";

				// Check if the class exists yet as an autoloadable class
				if (!class_exists($className)) {
					// Class should have been registered by bootstrap
					$this->logMessages[] = "Class: $className not found in loader:";
					$this->logMessages[] = print_r($this->modx::getLoader()->getPrefixesPsr4(), true);
					return $this->failure("Error, Validate 'bootstrap.php':  Class still does not exist after autoloader registration: $className");
				}
			}

			// Proceed if we have classes
			$this->logMessages[] = "Checking class: $className for table: " . $this->modx->getTableName($className);

			// Handle tables
        	// Code modified from: https://github.com/bezumkin/modExtra/blob/master/_build/resolvers/tables.php
			$table = $this->modx->getTableName($className);
            $newTable = true;
            if ($table) {
                $sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
                $this->logMessages[] = $sql;
                $stmt = $this->modx->prepare($sql);
                if ($stmt->execute() && $stmt->fetchAll()) {
                    $newTable = false;
                }
            }

            // If the table is new, create it
            if ($newTable) {
                $this->logMessages[] = "Creating table for class: $className";
				try {
					if (!$manager->createObjectContainer($className)) {
						$this->logMessages[] = "Failed to create table.";
					}
				} 
				catch (Exception $e) {
					$this->logMessages[] = "FATAL ERROR: ".$e->getMessage();
				}
                
            } else {
                /**
				 * If the table exists, check and update columns and indexes.
				 */
                $this->logMessages[] = 'Table exists, checking columns...';

                // Fetch any matching columns in the table and convert to an array
                $tableFields = [];
                $c = $this->modx->prepare("SHOW COLUMNS IN {$this->modx->getTableName($className)}");
                $c->execute();
                while ($cl = $c->fetch(PDO::FETCH_ASSOC)) 
				{
                    $tableFields[$cl['Field']] = $cl['Field'];
                }
                $this->logMessages[] = "Altering table fields: " . print_r($tableFields, true);

                // Loop through the fields defined in the MODX class file
                foreach ($this->modx->getFields($className) as $field => $v) 
				{
					// If the field exists in the database already
                    if (in_array($field, $tableFields)) 
					{
                        // Alter the field
                        unset($tableFields[$field]);
                        $manager->alterField($className, $field);
                    } 
					else 
					{
                        // This is a new field, add it
						$this->logMessages[] = "Adding Column: $field to table {$this->modx->getTableName($className)}";
                        $manager->addField($className, $field);
                    }
                }

                /**
				 * If there are any database fields that weren't "unset" above,
				 * it means they no longer exist in the schema, remove them
				 */ 
                foreach ($tableFields as $field) {
                    $manager->removeField($className, $field);
                }

                // Get any indexes and add to an array
                $this->logMessages[] = "Table exists, checking indexes...";
                $indexes = [];
                $c = $this->modx->prepare("SHOW INDEX FROM {$this->modx->getTableName($className)}");
                $c->execute();
                while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
                    $name = $row['Key_name'];
                    if (!isset($indexes[$name])) {
                        $indexes[$name] = [$row['Column_name']];
                    } else {
                        $indexes[$name][] = $row['Column_name'];
                    }
                }

                // Loop through the index array
                foreach ($indexes as $name => $values) {
                    sort($values);
                    $indexes[$name] = implode(':', $values);
                }

                // Get the defined indexes based on the schema
                $map = $this->modx->getIndexMeta($className);

                // Remove old indexes
                foreach ($indexes as $key => $index) {
                    // If the index is not in the map
                    if (!isset($map[$key])) {
                        // Remove the old index
                        if ($manager->removeIndex($className, $key)) {
                            $this->logMessages[] = "Removed index \"{$key}\" of the table \"{$className}\"";
                        }
                    }
                }

                // Add or alter existing indexes
                foreach ($map as $key => $index) {
                    ksort($index['columns']);
                    $index = implode(':', array_keys($index['columns']));
                    if (!isset($indexes[$key])) {
                        if ($manager->addIndex($className, $key)) {
                            $this->logMessages[] = "Added index \"{$key}\" in the table \"{$className}\"";
                        }
                    } else {
                        if ($index != $indexes[$key]) {
                            if ($manager->removeIndex($className, $key) && $manager->addIndex($className, $key)) {
                                $this->logMessages[] = "Updated index \"{$key}\" of the table \"{$className}\"";
                            }
                        }
                    }
                }
            }
        }
        $this->logMessages[] = "End table processing";
    }

    /**
     * Generate the Schema XML for xPDO
     *
     * Use the Package > Object > Field definitions
     * to generate the schema file.
     */
    public function generateSchema()
    {
        // XML Templates
        $packageTpl = '<model package="{package_key}" baseClass="{base_class}" platform="{platform}" defaultEngine="{default_engine}" phpdoc-package="{phpdoc_package}" version="{version}">';
        $objectTpl = '<object class="{class}" table="{table_name}" extends="{extends}">';
        
        $indexTpl = '<index alias="{column_name}" name="{column_name}" {index_attributes} type="{index}">
					<column key="{column_name}" length="" collation="A" null="false"/>
					</index>';
        $relTpl = '<{relation_type} alias="{alias}" class="{class}" local="{local}" foreign="{foreign}" cardinality="{cardinality}" owner="{owner}"/>';

		// Define field types
		$fieldTplArr = [
			'default' => '<field key="{column_name}" dbtype="{dbtype}" precision="{precision}" phptype="{phptype}" null="{allownull}" default="{default}"/>',
			'datetime' => '<field key="{column_name}" dbtype="{dbtype}" phptype="{phptype}" null="{allownull}"/>',
			'text' => '<field key="{column_name}" dbtype="{dbtype}" phptype="{phptype}" null="{allownull}" default="{default}"/>'
		];
		$fieldTplArr = array_merge(
			$fieldTplArr,
			array_fill_keys([
				'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob'
			], $fieldTplArr['text'])
		);
		$this->logMessages[] = "Field template array: ".print_r($fieldTplArr, true);

        // Start the schema
        $this->logMessages[] = "Generating schema...";
        $xmlSchema = '<?xml version="1.0" encoding="UTF-8"?>';

        // Replace package details
        $xmlSchema .= $this->eb->replaceValues($this->package->toArray(), $packageTpl);

        // Add Objects
        $objects = $this->package->getMany('Objects');
        if ($objects) {
            foreach ($objects as $object) {
                // Start an object entry
                $xmlSchema .= $this->eb->replaceValues($object->toArray(), $objectTpl);

                // Get the child fields and relationships
                $fields = $object->getMany('Fields');
                $rels = $object->getMany('Rels');
                if ($fields) {
					$this->logMessages[] = $object->get('class').'|'.$object->get('table_name').": Looping through fields: " . count($fields);
					$indexBlock = "";
                    foreach ($fields as $field) {
                        // Attempt dynamic template or fall back on default
                        $dbtype = strtolower($field->get('dbtype'));
                        $tpl = array_key_exists($dbtype,$fieldTplArr) ? $fieldTplArr[$dbtype] : $fieldTplArr['default'];

                        // Populate the field row
                        $fieldXml = $this->eb->replaceValues($field->toArray(), $tpl);

                        // Add on extra and generated if populated
                        if ($field->get('generated')) {
                            $fieldXml = str_replace('/>', " generated=\"{$field->get('generated')}\"/>", $fieldXml);
                        }
                        if ($field->get('extra')) {
                            $fieldXml = str_replace('/>', " generated=\"{$field->get('extra')}\"/>", $fieldXml);
                        }

                        // Add to the xml
                        $xmlSchema .= $fieldXml;

                        // If an index value is set
                        if ($field->get('index')) {
                            // Populate the index row, override BTREE2 and BTREE3 to just BTREE
							$fieldArray = $field->toArray();
							if (strpos($fieldArray['index'], 'BTREE') !== false) {
								$fieldArray['index'] = 'BTREE';
							}
                            $indexBlock .= $this->eb->replaceValues($fieldArray, $indexTpl);
                        }
                    }

					// If we have indexes, add those
					if ($indexBlock) {
						$xmlSchema .= $indexBlock;
					}
                }
                if ($rels) {
                    foreach ($rels as $rel) {
                        // Populate the field row
                        $xmlSchema .= $this->eb->replaceValues($rel->toArray(), $relTpl);
                    }
                }

                // Add any manual / raw xml
                if ($rawXml = $object->get('raw_xml')) {
                    $xmlSchema .= $rawXml;
                }
 
                // Close the object
                $xmlSchema .= '</object>';
            }
        }

        // Close the model
        $xmlSchema .= '</model>';

        // Format the xml
        $xmlDocument = new DOMDocument('1.0');
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        $xmlDocument->loadXML($xmlSchema);
        $xmlFormatted = $xmlDocument->saveXML();

        // Return the final XML
        return $xmlFormatted;
    }

    /**
     * Drop associated model tables
     *
     */
    public function dropModelTables()
    {
        // Get the manager
        $manager = $this->modx->getManager();

        // Get the related object entries and loop through
        $objectEntries = $this->package->getMany('Objects');
        $dropErrors = [];
        if ($objectEntries) {
            foreach ($objectEntries as $entry) {
				// Get the class based on version
                $className = $entry->get('class');
				if ($this->eb->isV3) {
					$className = $this->packageConfig['classPrefix'].$className;
				}
                if ($className) {
                    // Get the xpdo manager and drop the table
					$this->logMessages[] = "Dropping table for class: {$className}";
					try {
						if (!$manager->removeObjectContainer($className)) {
							// Add to the error list
							$dropErrors[] = $className;
						}
					}
                    catch (Exception $e) {
						$this->logMessages[] = "FATAL ERROR: ".$e->getMessage();
					}
                }
            }

			// If we had drop errors
			if (count($dropErrors) > 0) {
				$this->logMessages[] = "Drop errors: ".print_r($dropErrors, true);
			}
        } else {
            $this->failure('Unable to determine tables to drop. Please remove manually.');
        }
    }

	public function getLanguageTopics()
    {
        return $this->languageTopics;
    }
}