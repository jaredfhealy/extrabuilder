<?php

//v3 only
namespace ExtraBuilder\Processors\ebPackage;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modX;
use DOMDocument;
use SimpleXMLElement;
use PDO;
use Exception;
//v3 only

/**
 * Handle all build options
 *
 */
class PkgBuild extends Processor
{
    /** 
	 * Current ebPackage object
	 * 
	 * @var ExtraBuilder\Model\ebPackage $package
	 */
	public $package;
	public $languageTopics = array('extrabuilder:default');
    public $logMessages = [];

	/**
	 * PHP Namespace for 3.0 classes (Camel/PascalCase)
	 *
	 * @var string
	 */
	public $phpNamespace = "";

	/**
	 * Component (MODX) namespace (lowercase)
	 * 
	 * Used in menus, navigation, and mapping the
	 * core and asset paths for a MODX Extra.
	 *
	 * @var string $cmpNamespace
	 */
	public $cmpNamespace = "";

	/**
	 * The package attribute of your schema
	 * 
	 * MODX uses this to determine the build directory
	 * of your model class files.
	 * 
	 * In 2.x
	 *  - mycomponent.model = {core_path}components/mycomponent/model/
	 * 
	 * In 3.x
	 *  - mycomponent\src\Model = {core_path}components/mycomponent/src/Model/
	 *
	 * @var string $packageKey
	 */
	public $packageKey = "";

	/**
	 * 3.0 Specific options for parsing the schema
	 *
	 * @var array $schemaOptions
	 */
	public $schemaOptions = [];

    // Define the needed paths/directories
    public $packageBasePath = "";
    public $schemaPath = "";
    public $classPath = "";
	public $modelPath = "";
    public $assetsPath = "";
    public $schemaFilePath = "";

	/**
	 * Config for the current package being built
	 * 
	 * @var array $packageConfig
	 */
	public $packageConfig = [
		'corePath' => '{core_path}components/{cmp_namespace}/',
		'sourcePath' => '{core_path}components/{cmp_namespace}/src/',
		'modelPath2' => '{core_path}components/{cmp_namespace}/v2/model/',
		'modelPath3' => '{core_path}components/{cmp_namespace}/src/Model/',
		'publicAssetsPath' => '{assets_path}components/{cmp_namespace}/',
		'coreAssetsPath' => '{core_path}components/{cmp_namespace}/assets/',
		'schemaPath2' => '{core_path}components/{cmp_namespace}/v2/schema/',
		'schemaPath3' => '{core_path}components/{cmp_namespace}/schema/',
		'schemaFileName' => '{cmp_namespace}.mysql.schema.xml',
		'schemaFilePath' => '',
		'classPrefix' => ''
	];

    /**
     * @var boolean $previewOnly Deafult to preview only
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
        $this->schemaOptions['backupElements'] = $backupElements = $this->getProperty('backup_elements') === 'true';
        $this->schemaOptions['buildSkip'] = $buildSkip = $this->getProperty('build_skip') === 'true';
        $this->schemaOptions['buildDelete'] = $buildDelete = $this->getProperty('build_delete') === 'true';
        $this->schemaOptions['buildDeleteAndDrop'] = $buildDeleteAndDrop = $this->getProperty('build_delete_drop') === 'true';

        // Get the object using the passed in primary id
        $packageId = $this->getProperty('id', false);
        $this->package = $this->modx->getObject($this->eb->getClass('ebPackage'), $packageId);
        if (!$this->package) {
            // Return here
            return $this->failure("Unable to retrieve package with supplied ID: $packageId");
        } else {
            $this->logMessages[] = "Found package by id $packageId. Name: " . $this->package->get('display') . ", Package: " . $this->package->get('package_key');
        }

        // Get package key and paths
        $this->packageKey = $this->package->get('package_key');

		// Set the core and assets paths if different
        if ($corePath = $this->package->get('core_path')) {
			$this->packageConfig['corePath'] = $corePath;
		}
        if ($assetsPath = $this->package->get('assets_path')) {
			$this->packageConfig['corePath'] = $assetsPath;
		}

		// Set the build namespace for v3
		if ($this->eb->isV3) {
			// Package key should be the PHP Namespace MyComp\Model
			$this->packageConfig['classPrefix'] = rtrim($this->packageKey, '\\') . '\\';
			$this->phpNamespace = explode('\\', $this->packageKey)[0];

			// Set the component (modx) namespace
			$this->cmpNamespace = strtolower($this->phpNamespace);
		}
		else {
			$this->phpNamespace = explode('.', $this->packageKey)[0];
			$this->cmpNamespace = strtolower($this->phpNamespace);
		}

		// Map replacement keys for all paths
		$mapKeys = [
			'core_path' => MODX_CORE_PATH,
			'base_path' => MODX_BASE_PATH,
			'assets_path' => MODX_ASSETS_PATH,
			'package_key' => $this->packageKey,
			'cmp_namespace' => $this->cmpNamespace,
			'php_namespace' => $this->phpNamespace
		];

		// Loop through the packageConfig and replace values for each
		foreach ($this->packageConfig as $key => $tpl) {
			$this->packageConfig[$key] = $this->replaceValues($mapKeys, $tpl);
		}
		$this->packageConfig['schemaFilePath'] = $this->packageConfig['schemaPath'.$this->eb->version] . $this->packageConfig['schemaFileName'];
		$this->eb->logDebug(print_r($this->packageConfig, true));

        // Set preview to false
        if ($buildSkip || $buildDelete || $buildDeleteAndDrop) {
            $this->previewOnly = false;
        }

        // Handle schema deletion
        if ($buildDelete && !$this->eb->isV3) {
            // Delete the schema files
            $this->deleteSchemaFiles();
        }

		// If delete and drop was selected
        if ($buildDeleteAndDrop) {
			// Drop all tables
            $this->dropModelTables();
        }

        // Generate the schema as long as it's not for the package builder
        $schema = $this->generateSchema();

        if ($writeSchemaOnly || !$this->previewOnly) {
            // Make sure at least the schema directory exists
			$schemaPathKey = 'schemaPath'.$this->eb->version;
			$schemaDir = $this->packageConfig[$schemaPathKey];
            if (!is_dir($schemaDir)) {
                mkdir($schemaDir, 0775, true);
            }

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
		 * Make the assets directories if it doesn't exist
		 * 
		 * ExtraBuilder uses a 'symlink' pointing:
		 *  from: {root_path}assets/components/MyComponent/
		 *  to: {core_path}MyComponent/assets/
		 * 
		 * This allows you to have one directory within the
		 * core components directory for all the files and
		 * resources needed for your Extra. This also effectively
		 * makes part of your core directory web accessible.
		 * 
		 * When you build the transport package, the assests
		 * will be installed to the standard assets directory.
		 */

		// First check for an assets directory in core
		if (!is_dir($this->packageConfig['coreAssetsPath'])) {
			mkdir($this->packageConfig['coreAssetsPath'], 0775, true);
		}
        if (!is_dir($this->packageConfig['publicAssetsPath'])) {
			// Remove the slash from publicAssetsPath to use as our symlink name
			$linkName = substr($this->packageConfig['publicAssetsPath'], 0, -1);
			$this->logMessages[] = "Creating symlink from: ".$linkName.", to: ".$this->packageConfig['coreAssetsPath'];
            $this->logMessages[] = "Symlink created: ".(symlink($this->packageConfig['coreAssetsPath'], $linkName) == true ? 'True' : 'False');
        }

		// Create the namespace first in v3 since it plays a key role
		$this->logMessages[] = "Creating Namespace: {$this->cmpNamespace}";
        $namespace = $this->modx->getObject($this->eb->getClass('modNamespace'), ['name' => $this->cmpNamespace]);
        if (!$namespace) {
			// Generate a new namespace
			$namespace = $this->modx->newObject($this->eb->getClass('modNamespace'), [ 
                'path' => "{core_path}components/{$this->cmpNamespace}/",
                'assets_path' => "{assets_path}components/{$this->cmpNamespace}/",
            ]);
			$namespace->set('name', $this->cmpNamespace);

			// Save the new namespace
            if ($namespace->save()) {
                $this->logMessages[] = "Namespace created successfully {$this->cmpNamespace}...";
            }
        } else {
            $this->logMessages[] = "Namespace, {$this->cmpNamespace}, already exists. Use the manager to update.";
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
					$lexiconContent = str_replace('{$namespace}', $this->phpNamespace, $lexiconContent);

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
        // Load the transport package class
        //$this->modx->loadClass('transport.modPackageBuilder', '', false, true);

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
				"namespacePrefix" => $this->phpNamespace.'\\'
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
				$schemaContents = file_get_contents($this->packageConfig['schemaFilePath']);
				if (strpos('.v2.model') === false) {
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
			$contents = str_replace('{$namespace}', $this->phpNamespace, $contents);
			file_put_contents($this->packageConfig['corePath'].'bootstrap.php', $contents);

			// Set variables needed by the bootstrap file
			$modx =& $this->modx;
			
			// Set namespace as an array so it can be accessed in bootstrap.php
			$namespace = $namespace->toArray();

			// Include the newly generated bootstrap file to register our classes
			@include $this->packageConfig['corePath'].'bootstrap.php';
		}
		else if (!$this->eb->isV3) {
			// In v2 use add package, expecting packageKey format: <mycomponent>.v2.model
			$this->modx->addPackage($this->packageKey, MODX_CORE_PATH.'components/');
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
                $this->logMessages[] = "Table Fields: " . print_r($tableFields, true);

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
        
        $indexTpl = '<index alias="{column_name}" name="{column_name}" primary="false" unique="false" type="{index}">
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
        $xmlSchema .= $this->replaceValues($this->package->toArray(), $packageTpl);

        // Add Objects
        $objects = $this->package->getMany('Objects');
        if ($objects) {
            foreach ($objects as $object) {
                // Start an object entry
                $xmlSchema .= $this->replaceValues($object->toArray(), $objectTpl);

                // Get the child fields and relationships
                $fields = $object->getMany('Fields');
                $rels = $object->getMany('Rels');
                if ($fields) {
					$this->logMessages[] = $object->get('table_name').": Looping through fields: " . count($fields);
                    foreach ($fields as $field) {
                        // Attempt dynamic template or fall back on default
                        $dbtype = strtolower($field->get('dbtype'));
                        $tpl = array_key_exists($dbtype,$fieldTplArr) ? $fieldTplArr[$dbtype] : $fieldTplArr['default'];

                        // Populate the field row
                        $fieldXml = $this->replaceValues($field->toArray(), $tpl);

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
                            // Populate the index row
                            $xmlSchema .= $this->replaceValues($field->toArray(), $indexTpl);
                        }
                    }
                }
                if ($rels) {
                    foreach ($rels as $rel) {
                        // Populate the field row
                        $xmlSchema .= $this->replaceValues($rel->toArray(), $relTpl);
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
     * Delete generated schema files
     *
     * Directory and contents:
     *  - <key>/model/<key>/mysql
     *
     * Delete files:
     *  - Schema File: <key>/model/<key>/<key>.mysql.schema.xml
     *  - Class files: <key>/model/<key>/
     *    - metadata.mysql.php
     *    - <key>objectclass.class.php
     */
    public function deleteSchemaFiles()
    {
        // First delete the directory
        $this->rrmdir($this->packageConfig['modelPath'.$this->eb->version] . 'mysql/');

        // Get the object entries for this package
        $objects = $this->package->getMany('Objects');
        if ($objects) {
            foreach ($objects as $object) {
                // Delete each corresponding table class file
                unlink($this->packageConfig['modelPath'.$this->eb->version] . strtolower($object->get('class')) . '.class.php');
            }
        }
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
                $className = $object->get('class');
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

    /**
     * Replace placeholders with values
     *
     */
    public function replaceValues($objectArray, $stringTpl)
    {
        $string = $stringTpl;
        foreach ($objectArray as $key => $value) {
            $string = str_replace('{' . $key . '}', $value, $string);
        }

        // Return the new string
        return $string;
    }

    /**
     * Delete directory recursively
     *
     */
    public function rrmdir($src)
    {
        $dir = opendir($src);
        if ($dir) {
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    $full = $src . '/' . $file;
                    if (is_dir($full)) {
                        $this->rrmdir($full);
                    } else {
                        unlink($full);
                    }
                }
            }

            closedir($dir);
            rmdir($src);
        }
    }

	public function getLanguageTopics()
    {
        return $this->languageTopics;
    }
}