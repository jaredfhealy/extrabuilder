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
    public $object;
	public $languageTopics = array('ExtraBuilder:default');
    public $logMessages = [];
	public $buildNamespace = "";
	public $packageKey = "";
	public $schemaOptions = [];

    // Define the needed paths/directories
    public $packageBasePath = "";
    public $schemaPath = "";
    public $classPath = "";
	public $modelPath = "";
    public $assetsPath = "";
    public $schemaFilePath = "";

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
        $package = $this->modx->getObject($this->eb->getClass('ebPackage'), $packageId);
        if (!$package) {
            // Return here
            return $this->failure("Unable to retrieve package with supplied ID: $packageId");
        } else {
            $this->logMessages[] = "Found package by id $packageId. Name: " . $package->get('display') . ", Key: " . $package->get('package_key');
        }

		// Store a reference to the package object
		$this->object =& $package;

        // Get package key and paths
        $this->packageKey = $packageKey = $package->get('package_key');
        $corePath = $package->get('core_path') ? $package->get('core_path') : '{core_path}components/{package_key}/';
        $assetsPath = $package->get('assets_path') ? $package->get('assets_path') : '{assets_path}components/{package_key}/';

		// Set the build namespace for v3
		$this->buildNamespace = explode('\\', $packageKey)[0];
        
		// Replace values
        $corePath = str_replace('{core_path}', MODX_CORE_PATH, $corePath);
        $corePath = str_replace('{base_path}', MODX_BASE_PATH, $corePath);
        $corePath = str_replace('{package_key}', $this->buildNamespace, $corePath);
        $assetsPath = str_replace('{assets_path}', MODX_ASSETS_PATH, $assetsPath);
        $assetsPath = str_replace('{base_path}', MODX_BASE_PATH, $assetsPath);
        $assetsPath = str_replace('{package_key}', $this->buildNamespace, $assetsPath);

        // Setup the paths
		// {base_path}/core/components/{package_key}/
        $this->packageBasePath = $corePath;
		// {base_path}/core/components/{package_key}/schema/
        $this->schemaPath = $corePath . "schema/";
		// {base_path}/core/components/{package_key}/schema/{package_key}.mysql.schema.xml
        $this->schemaFilePath = $this->schemaPath . strtolower($this->buildNamespace) . '.mysql.schema.xml';
		// {base_path}/core/components/{package_key}/src/
        $this->sourcePath = $corePath . "src/";
		// {base_path}/core/components/{package_key}/src/
        $this->modelPath = $this->sourcePath . "Model/";
		// {assets_path}/components/{package_key}
        $this->assetsPath = $assetsPath;

        // Set preview to false
        if ($buildSkip || $buildDelete || $buildDeleteAndDrop) {
            $this->previewOnly = false;
        }

        // Handle deletion
        if ($buildDelete) {
            // Delete the schema files
            //$this->previewOnly = false;
            //$this->deleteSchemaFiles($package);
			//$this->rrmdir($this->modelPath);
			//return $this->failure("Pausing here");
        }
        if ($buildDeleteAndDrop) {
            // Delete the schema files and drop the tables
            //$this->deleteSchemaFiles($package);
			//$this->rrmdir($this->modelPath);
            $this->dropModelTables();
        }

        // Generate the schema as long as it's not for the package builder
        $schema = $this->generateSchema();

        if ($writeSchemaOnly || !$this->previewOnly) {
            // Make sure at least the schema directory exists
            if (!is_dir($this->schemaPath)) {
                mkdir($this->schemaPath, 0775, true);
            }
            // Create the schema file
            if (file_put_contents($this->schemaFilePath, $schema)) {
                // Written successfully
                $this->logMessages[] = "XML Schema written successfully...";
            } else {
                return $this->failure('Unable to write schema file: ' . $this->schemaFilePath, []);
            }
        }

        // If not preview only
        if (!$this->previewOnly) {
            // Call the build script
            $this->buildSchema();

            // If include vuecmp is set to true
            if ($this->object->get('vuecmp') === 'true') {
                $this->includeVueCmp();
            }
        }

        $separator = '' . PHP_EOL;
        return $this->success('', [
            'schema' => $schema,
            'core_path' => $corePath,
            'assets_path' => $assetsPath,
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
        // Make the assets directory if it doesn't exist
        if (!is_dir($this->assetsPath)) {
            mkdir($this->assetsPath, 0775, true);
        }

		// Set the sources array for logging
        $sources = array(
            'root' => MODX_BASE_PATH,
            'core' => $this->packageBasePath,
            'model' => $this->sourcePath.'/Model/',
            'assets' => $this->assetsPath,
            'schema' => $this->schemaPath,
        );

		// Create the namespace first in v3 since it plays a key role
		$this->logMessages[] = "Creating Namespace...";
        $namespace = $this->modx->getObject('modNamespace', ['name' => $this->buildNamespace]);
        if (!$namespace) {
            $namespace = $this->modx->newObject('modNamespace', [
                'path' => "{core_path}components/{$this->buildNamespace}/",
                'assets_path' => "{assets_path}components/{$this->buildNamespace}/",
            ]);
            $namespace->set('name', $this->buildNamespace);
            if ($namespace->save()) {
                $this->logMessages[] = "Namespace created successfully {$this->buildNamespace}...";
            }
        } else {
            $this->logMessages[] = "Namespace, {$this->buildNamespace}, already exists. Use the manager to update.";
        }

        // If include lexicon is set to true
        $lexiconPath = '';
        if ($this->object->get('lexicon') === 'true') {
            // Make sure the directory exists
            $lexiconPath = $this->packageBasePath . 'lexicon/en/';
            if (!is_dir($lexiconPath)) {
                mkdir($lexiconPath, 0775, true);
            }
			$sources['lexicon'] = $lexiconPath;

            // If the file does not exist yet.
            if (!is_file($lexiconPath . 'default.inc.php')) {
                // Copy the default lexicon file
                copy(MODX_CORE_PATH . 'components/ExtraBuilder/lexicon/en/default.inc.php', $lexiconPath . 'default.inc.php');
            }
        }

		// Log out all the calculated paths
		$this->logMessages[] = "Sources: " . print_r($sources, true);
        // Load the transport package class
        //$this->modx->loadClass('transport.modPackageBuilder', '', false, true);

        // Get the manager and generator
        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();

		// Set options for parse
		$parseOptions = [
			"compile" => 0,
			"update" => 0,
			"regenerate" => 0,
			"namespacePrefix" => $this->buildNamespace
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

		// If this is v3
		if ($this->isV3) {
			// Parse the schema using v3 options
			if ($generator->parseSchema(
				$this->schemaFilePath, 
				$this->sourcePath, 
				$parseOptions
			)) {
				$this->logMessages[] = "Schema processing complete, model files generated";
			}
			else {
				$this->failure("Unable to write model files");
				$this->logMessages[] = "Unable to write model files";
			}
		}
		else {
			// Parse v2
			$generator->parseSchema($this->schemaFilePath, $this->sourcePath);
		}
			

		// Check for a bootstrap.php file for this new extra
		if (!is_file($this->packageBasePath.'bootstrap.php')) {
			// Generate the file and return
			$contents = file_get_contents($this->eb->config['corePath'].'_build/templates/bootstrap.tpl');
			$contents = str_replace('{$namespace}', $this->buildNamespace, $contents);
			file_put_contents($this->packageBasePath.'bootstrap.php', $contents);

			// Return here with a message to re-run
			return $this->failure($this->packageBasePath.'bootstrap.php: generated on first run.<br/><br/>Please run the build again.');
		}

		// Get the child object records
		$objects = $this->object->getMany('Objects');
		if (!$objects) {
			return $this->failure('Unable to retrieve related objects/tables.');
		}

		// Loop through the tables
        foreach ($objects as $object) {
			// Convert class for MODX 3 if needed
			$className = $this->packageKey .$object->get('class');
			$this->logMessages[] = "Checking if class exists: $className";
            
			// Check if the class exists yet as an autoloadable class
			if (!class_exists($className)) {
				// Class should have been registered by bootstrap
				$this->logMessages[] = "Class: $className not found in loader:";
				$this->logMessages[] = print_r($loader->getPrefixesPsr4(), true);
				return $this->failure("Error, Validate 'bootstrap.php':  Class still does not exist after autoloader registration: $className");
			}

			// Proceed if we have classes
			$this->logMessages[] = "Checking class: $className for table: " . $this->modx->getTableName($className);

			// Handle tables
        	// Code from: https://github.com/bezumkin/modExtra/blob/master/_build/resolvers/tables.php
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
        $packageTpl = '<model package="{package_key}" baseClass="{base_class}" platform="{platform}" defaultEngine="{default_engine}" phpdoc-package="{phpdoc_package}" phpdoc-subpackage="{phpdoc_subpackage}" version="1.1">';
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
        $xmlSchema .= $this->replaceValues($this->object->toArray(), $packageTpl);

        // Add Objects
        $objects = $this->object->getMany('Objects');
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
        //$xmlFormatted = $schemaStart.PHP_EOL.$xmlFormatted;
        return $xmlFormatted;
    }

    /**
     * Include Vue.js components and starting files
     * to use the Vue CMP and iFrame methodology
     */
    public function includeVueCmp()
    {
        // Asset Target directories and files
        $cssPath = $this->assetsPath . 'css/';
        $jsPath = $this->assetsPath . 'js/';
        $connectorFilePath = $this->assetsPath . 'connector.php';

        // Only generate these if they don't exist already
        // If the css, js directories don't exist
        if (!is_dir($cssPath) && !is_dir($jsPath) && !is_file($connectorFilePath)) {
            // Define the source directories and files
            $sourceCssPath = MODX_CORE_PATH . 'components/extrabuilder/_build/cmpexample/assets/css/';
            $sourceJsPath = MODX_CORE_PATH . 'components/extrabuilder/_build/cmpexample/assets/js/';
            $sourceConnectorFilePath = MODX_CORE_PATH . 'components/extrabuilder/_build/cmpexample/assets/connector.php';

            // Copy the directories
            $this->modx->extrabuilder->copydir($sourceCssPath, $this->assetsPath);
            $this->modx->extrabuilder->copydir($sourceJsPath, $this->assetsPath);

            // Copy the connector
            copy($sourceConnectorFilePath, $connectorFilePath);
        }

        // Core files and directories
        $controllerPath = $this->packageBasePath . 'controllers/';
        $templatePath = $this->packageBasePath . 'templates/';

        // Only create if they don't exist yet
        if (!is_dir($controllerPath) && !is_dir($templatePath)) {
            mkdir($controllerPath, 0775);
            mkdir($templatePath, 0775);

            // Template sources
            $indexControllerPath = MODX_CORE_PATH . 'components/extrabuilder/_build/cmpexample/controllers/index.class.php.tpl';
            $indexHtmlFilePath = MODX_CORE_PATH . 'components/extrabuilder/_build/cmpexample/templates/index.html';

            // Get the template contents
            $indexController = file_get_contents($indexControllerPath);
            $indexController = str_replace('[[+package_class]]', ucfirst($this->object->get('package_key')), $indexController);

            // Write the controller file
            file_put_contents($controllerPath . 'index.class.php', $indexController);

            // Copy the index.html file
            copy($indexHtmlFilePath, $templatePath . 'index.html');
        }
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
    public function deleteSchemaFiles($package)
    {
        // First delete the directory
        $this->rrmdir($this->classPath . 'mysql/');

        // Get the object entries for this package
        $objects = $package->getMany('Objects');
        if ($objects) {
            foreach ($objects as $object) {
                // Delete each corresponding table class file
                unlink($this->classPath . strtolower($object->get('class')) . '.class.php');
            }
        }

        // Delete the schema file
        //unlink($this->classPath.'metadata.mysql.php');
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
        $objectEntries = $this->object->getMany('Objects');
        $dropErrors = [];
        if ($objectEntries) {
            foreach ($objectEntries as $entry) {
                $className = $entry->get('class');
                if ($className) {
                    // Get the xpdo manager and drop the table
					$this->logMessages[] = "Dropping table for class: {$this->packageKey}\\{$className}";
					try {
						if (!$manager->removeObjectContainer($this->packageKey . '\\' .  $className)) {
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