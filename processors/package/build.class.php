<?php

/**
 * Build the package schema
 *
 * @package extrabuilder
 * @subpackage processors.package
 */
class ExtrabuilderBuildPackageProcessor extends modObjectProcessor
{
	public $classKey = 'ebPackage';
	public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.package';
	public $logMessages = [];

	// Define the needed paths/directories
	public $packageBasePath = "";
	public $schemaPath = "";
	public $classPath = "";
	public $assetsPath = "";
	public $schemaFilePath = "";

	/**
	 * Default to preview only
	 */
	public $previewOnly = true;

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Get parameters to determine actions
		$writeSchemaOnly = $this->getProperty('write_schema') === 'true';
		$backupElements = $this->getProperty('backup_elements') === 'true';
		$buildSkip = $this->getProperty('build_skip') === 'true';
		$buildDelete = $this->getProperty('build_delete') === 'true';
		$buildDeleteAndDrop = $this->getProperty('build_delete_drop') === 'true';

		// Get the object
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		$this->object = $this->modx->getObject($this->classKey, $primaryKey);
		$package = $this->object;
		$packageId = $package->get('id');
		if (!$package) {
			// Return here
			return $this->failure('Unable to retrieve package with supplied ID.');
		}
		else {
			$this->logMessages[] = "Found package by id $packageId. Name: " . $package->get('display') . ", Key: " . $package->get('package_key');
		}

		// Get package key and paths
		$packageKey = $package->get('package_key');
		$corePath = $package->get('core_path') ? $package->get('core_path') : '{core_path}components/{package_key}/';
		$assetsPath = $package->get('assets_path') ? $package->get('assets_path') : '{assets_path}components/{package_key}/';

		// Replace values
		$corePath = str_replace('{core_path}', MODX_CORE_PATH, $corePath);
		$corePath = str_replace('{base_path}', MODX_BASE_PATH, $corePath);
		$corePath = str_replace('{package_key}', $packageKey, $corePath);
		$assetsPath = str_replace('{assets_path}', MODX_ASSETS_PATH, $assetsPath);
		$assetsPath = str_replace('{base_path}', MODX_BASE_PATH, $assetsPath);
		$assetsPath = str_replace('{package_key}', $packageKey, $assetsPath);

		// Setup the paths
		$this->packageBasePath = $corePath;
		$this->schemaPath = $corePath . "model/schema/";
		$this->schemaFilePath = $this->schemaPath . $packageKey . '.mysql.schema.xml';
		$this->classPath = $corePath . "model/$packageKey/";
		$this->assetsPath = $assetsPath;

		// Build skip
		if ($buildSkip) {
			$this->previewOnly = false;
		}

		// Handle deletion
		if ($buildDelete) {
			// Delete the schema files
			$this->previewOnly = false;
			$this->deleteSchemaFiles($package);
		}
		if ($buildDeleteAndDrop) {
			// Delete the schema files and drop the tables
			$this->deleteSchemaFiles($package);
			$this->dropModelTables($package);
		}

		// Generate the schema as long as it's not for the package builder
		$schema = $this->generateSchema($package);

		if ($writeSchemaOnly || !$this->previewOnly) {
			// Make sure at least the schema directory exists
			if (!is_dir($this->schemaPath)) {
				mkdir($this->schemaPath, 0775, true);
			}
			// Create the schema file
			if (file_put_contents($this->schemaFilePath, $schema)) {
				// Written successfully
				$this->logMessages[] = "XML Schema written successfully...";
			}
			else {
				return $this->failure('Unable to write schema file: '.$this->schemaFilePath, []);
			}
		}

		// If not preview only
		if (!$this->previewOnly) {
			// Call the build script
			$this->buildSchema($packageKey);

			// If include vuecmp is set to true
			if ($this->object->get('vuecmp') === 'true') {
				$this->includeVueCmp();
			}
		}

		$separator = ''.PHP_EOL;
		return $this->success('', [
			'schema' => $schema,
			'core_path' => $corePath,
			'assets_path' => $assetsPath,
			'messages' => implode($separator, $this->logMessages)
		]);
	}

	/**
	 * Build the schema files, create tables
	 * 
	 * Resources:
	 *  - https://github.com/bezumkin/modExtra (build script)
	 * 
	 * @param string $schema The XML xPDO Schema model
	 * @param object $package The xPDO ebPackage Object
	 * @return string Log messages
	 */
	public function buildSchema($packageKey)
	{
		// Make sure directories exist
		if (!is_dir($this->schemaPath)) {
			mkdir($this->schemaPath, 0775, true);
		}
		if (!is_dir($this->classPath)) {
			mkdir($this->classPath, 0775, true);
		}
		if (!is_dir($this->assetsPath)) {
			mkdir($this->assetsPath, 0775, true);
		}

		// If include lexicon is set to true
		$lexiconPath = '';
		if ($this->object->get('lexicon') === 'true') {
			// Make sure the directory exists
			$lexiconPath = $this->packageBasePath.'lexicon/en/';
			if (!is_dir($lexiconPath)) {
				mkdir($lexiconPath, 0775, true);
			}

			// If the file does not exist yet.
			if (!is_file($lexiconPath.'default.inc.php')) {
				// Copy the default lexicon file
				copy(MODX_CORE_PATH.'components/extrabuilder/lexicon/en/default.inc.php', $lexiconPath.'default.inc.php');
			}
		}

		// Begin the build script
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tstart = $mtime;
		set_time_limit(0);

		// Load the transport package class
		$this->modx->loadClass('transport.modPackageBuilder', '', false, true);

		// Set the sources array
		$sources = array(
			'root' => MODX_BASE_PATH,
			'core' => $this->packageBasePath,
			'model' => $this->packageBasePath . 'model/',
			'assets' => $this->assetsPath,
			'schema' => $this->schemaPath,
		);
		if ($lexiconPath) {
			$sources['lexicon'] = $lexiconPath;
		}
		$this->logMessages[] = "Sources: ".print_r($sources, true);

		// Get the manager and generator
		$manager = $this->modx->getManager();
		$generator = $manager->getGenerator();

		// Parse the schema
		$generator->parseSchema($this->schemaFilePath, $sources['model']);
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tend = $mtime;
		$totalTime = ($tend - $tstart);
		$totalTime = sprintf("%2.4f s", $totalTime);
		$this->logMessages[] = "Schema Finished... Execution time: $totalTime";

		// Handle tables
		// Code from: https://github.com/bezumkin/modExtra/blob/master/_build/resolvers/tables.php
		$this->modx->addPackage($packageKey, $sources['model']);
		$objects = [];
		$this->logMessages[] = "Parsing Schema ".$this->schemaFilePath;
		if (is_file($this->schemaFilePath)) {
			$schema = new SimpleXMLElement($this->schemaFilePath, 0, true);
			if (isset($schema->object)) {
				foreach ($schema->object as $obj) {
					$objects[] = (string)$obj['class'];
				}
			}
			unset($schema);
		}

		foreach ($objects as $class) {
			$this->logMessages[] = "Checking class: $class for table, ".$this->modx->getTableName($class);
			$table = $this->modx->getTableName($class);
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
				$this->logMessages[] = "Creating table for class: $class";
				if (!$manager->createObjectContainer($class)) {
					$this->logMessages[] = "Failed to create table.";
				}
			} else {
				// If the table exists
				// 1. Operate with tables
				$this->logMessages[] = 'Table exists, checking columns...';

				// Fetch any matching columns in the table and convert to an array
				$tableFields = [];
				$c = $this->modx->prepare("SHOW COLUMNS IN {$this->modx->getTableName($class)}");
				$c->execute();
				while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
					$tableFields[$cl['Field']] = $cl['Field'];
				}
				$this->logMessages[] = "Table Fields: ".print_r($tableFields, true);

				// Loop throught the fields for this table
				foreach ($this->modx->getFields($class) as $field => $v) {
					if (in_array($field, $tableFields)) {
						// The field exists, alter it
						unset($tableFields[$field]);
						$manager->alterField($class, $field);
					} else {
						// The field does not exist, add it
						$manager->addField($class, $field);
					}
				}

				// If there are any fields that weren't "unset" above,
				// it means they no longer exist in the schema, remove them
				foreach ($tableFields as $field) {
					$manager->removeField($class, $field);
				}

				// 2. Get any indexes and add to an array
				$this->logMessages[] = "Table exists, checking indexes...";
				$indexes = [];
				$c = $this->modx->prepare("SHOW INDEX FROM {$this->modx->getTableName($class)}");
				$c->execute();
				while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
					$name = $row['Key_name'];
					if (!isset($indexes[$name])) {
						$indexes[$name] = [$row['Column_name']];
					} else {
						$indexes[$name][] = $row['Column_name'];
					}
				}

				// Loop through the index away
				foreach ($indexes as $name => $values) {
					sort($values);
					$indexes[$name] = implode(':', $values);
				}

				// Get the defined indexes based on the schema
				$map = $this->modx->getIndexMeta($class);

				// Remove old indexes
				foreach ($indexes as $key => $index) {
					// If the index is not in the map
					if (!isset($map[$key])) {
						// Remove the old index
						if ($manager->removeIndex($class, $key)) {
							$this->logMessages[] = "Removed index \"{$key}\" of the table \"{$class}\"";
						}
					}
				}
				
				// Add or alter existing
				foreach ($map as $key => $index) {
					ksort($index['columns']);
					$index = implode(':', array_keys($index['columns']));
					if (!isset($indexes[$key])) {
						if ($manager->addIndex($class, $key)) {
							$this->logMessages[] = "Added index \"{$key}\" in the table \"{$class}\"";
						}
					} else {
						if ($index != $indexes[$key]) {
							if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
								$this->logMessages[] = "Updated index \"{$key}\" of the table \"{$class}\"";
							}
						}
					}
				}
			}
		}
		$this->logMessages[] = "End table processing";

		$this->logMessages[] = "Creating Namespace...";
		$namespace = $this->modx->getObject('modNamespace', ['name' => $packageKey]);
		if (!$namespace) {
			$namespace = $this->modx->newObject('modNamespace', [
				'path' => "{core_path}components/$packageKey/",
				'assets_path' => "{assets_path}components/$packageKey/"
			]);
			$namespace->set('name', $packageKey);
			if ($namespace->save()) {
				$this->logMessages[] = 'Namespace created successfully...';
			}
		} else {
			$this->logMessages[] = 'Namespace already exists...';
		}
	}

	/**
	 * Generate the Schema XML for xPDO
	 * 
	 * Use the Package > Object > Field definitions
	 * to generate the schema file.
	 * 
	 * @param object $package The ebPackage xPDOSimpleObject
	 */
	public function generateSchema($package)
	{
		// Templates
		$packageTpl = '<model package="{package_key}" baseClass="{base_class}" platform="{platform}" defaultEngine="{default_engine}" phpdoc-package="{phpdoc_package}" phpdoc-subpackage="{phpdoc_subpackage}" version="1.1">';
		$objectTpl = '<object class="{class}" table="{table_name}" extends="{extends}">';
		$fieldTpl = '<field key="{column_name}" dbtype="{dbtype}" precision="{precision}" phptype="{phptype}" null="{allownull}" default="{default}"/>';
		$textTpl = '<field key="{column_name}" dbtype="{dbtype}" phptype="{phptype}" null="{allownull}" default="{default}"/>';
		$datetimeTpl = '<field key="{column_name}" dbtype="{dbtype}" phptype="{phptype}" null="{allownull}"/>';
		$indexTpl = '<index alias="{column_name}" name="{column_name}" primary="false" unique="false" type="{index}">
					<column key="{column_name}" length="" collation="A" null="false"/>
					</index>';
		$relTpl = '<{relation_type} alias="{alias}" class="{class}" local="{local}" foreign="{foreign}" cardinality="{cardinality}" owner="{owner}"/>';

		// Start the schema
		$this->logMessages[] = "Generating schema...";
		$xmlSchema = '<?xml version="1.0" encoding="UTF-8"?>';

		// Replace package details
		$xmlSchema .= $this->replaceValues($package->toArray(), $packageTpl);

		// Add Objects
		$objects = $package->getMany('Objects');
		if ($objects) {
			foreach ($objects as $object) {
				// Start an object entry
				$xmlSchema .= $this->replaceValues($object->toArray(), $objectTpl);

				// Get the child fields and relationships
				$fields = $object->getMany('Fields');
				$rels = $object->getMany('Rels');
				if ($fields) {
					foreach ($fields as $field) {
						// Attempt dynamic template or fall back on fieldTpl
						$dbtype = $field->get('dbtype');
						$tpl = ${$dbtype.'Tpl'};
						$tpl = isset($tpl) ? $tpl : $fieldTpl;

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
			file_put_contents($controllerPath.'index.class.php', $indexController);

			// Copy the index.html file
			copy($indexHtmlFilePath, $templatePath.'index.html');
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
		$this->rrmdir($this->classPath.'mysql/');

		// Get the object entries for this package
		$objects = $package->getMany('Objects');
		if ($objects) {
			foreach ($objects as $object) {
				// Delete each corresponding table class file
				unlink($this->classPath.strtolower($object->get('class')).'.class.php');
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
				$class = $entry->get('class');
				if ($class) {
					// Get the xpdo manager and drop the table
					if (!$manager->removeObjectContainer($class)) {
						// Add to the error list
						$dropErrors[] = $class;
					}
				}
			}
		}
		else {
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
			$string = str_replace('{'.$key.'}', $value, $string);
		}

		// Return the new string
		return $string;
	}

	/**
	 * Delete directory recursively
	 * 
	 */
	public function rrmdir($src) {
		$dir = opendir($src);
		if ($dir) {
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					$full = $src . '/' . $file;
					if ( is_dir($full) ) {
						$this->rrmdir($full);
					}
					else {
						unlink($full);
					}
				}
			}

			closedir($dir);
			rmdir($src);
		}
	}
}

return 'ExtrabuilderBuildPackageProcessor';
