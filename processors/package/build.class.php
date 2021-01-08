<?php

/**
 * Build the package schema
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvBuildPackageProcessor extends modObjectProcessor
{
	public $classKey = 'grvPackage';
	public $languageTopics = array('grv:default');
	public $objectType = 'grv.schema';
	public $logMessages = [];

	// Define the needed paths/directories
	public $packageBasePath = "";
	public $schemaPath = "";
	public $classPath = "";
	public $assetsPath = "";
	public $schemaFilePath = "";

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Get parameters to determine actions
		$packageId = $this->getProperty('package_id');
		$previewOnly = 'true'; // Default to preview
		$writeSchema = $this->getProperty('write_schema') === 'true';
		$buildSkip = $this->getProperty('build_skip') === 'true';
		$buildDelete = $this->getProperty('build_delete') === 'true';
		$buildDeleteAndDrop = $this->getProperty('build_delete_drop') === 'true';

		if (!empty($packageId)) {
			// Get the object
			$package = $this->modx->getObject('grvPackage', $packageId);
			if (!$package) {
				// Return here
				return $this->failure('Unable to retrieve package with supplied ID.', ['package_id' => $packageId]);
			}
			else {
				$this->logMessages[] = "Found package by id $packageId. Name: " . $package->get('display') . ", Key: " . $package->get('package_key');
			}
		}
		else {
			return $this->failure('No Package ID supplied.', ['package_id' => $packageId]);
		}

		// Get package key and paths
		$packageKey = $package->get('package_key');
		$this->packageBasePath = MODX_CORE_PATH . "components/$packageKey/";
		$this->schemaPath = $this->packageBasePath . "model/schema/";
		$this->schemaFilePath = $this->schemaPath . $packageKey . '.mysql.schema.xml';
		$this->classPath = $this->packageBasePath . "model/$packageKey/";
		$this->assetsPath = MODX_BASE_PATH . "assets/components/$packageKey/";

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

		// Build skip
		if ($buildSkip) {
			$previewOnly = false;
		}

		// Handle deletion
		if ($buildDelete) {
			// Delete the schema files
			$previewOnly = false;
			$this->deleteSchemaFiles($package);
		}
		if ($buildDeleteAndDrop) {
			// Delete the schema files and drop the tables
			$this->deleteSchemaFiles($package);
			$this->dropModelTables($package);
		}

		// Generate the schema as long as it's not for the package builder
		$schema = $this->generateSchema($package);

		if ($writeSchema) {
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
		if (!$previewOnly) {
			// Call the build script
			$this->buildSchema($packageKey);
		}

		$separator = ''.PHP_EOL;
		return $this->success('', [
			'schema' => $schema,
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
	 * @param object $package The xPDO grvPackage Object
	 * @return string Log messages
	 */
	public function buildSchema($packageKey)
	{
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
			
			// If the table is just created
			if ($newTable) {
				$this->logMessages[] = "Creating table for class: $class";
				if (!$manager->createObjectContainer($class)) {
					$this->logMessages[] = "Failed to create table.";
				}
			} else {
				// If the table exists
				// 1. Operate with tables
				$this->logMessages[] = 'Table exists, checking columns...';
				$tableFields = [];
				$c = $this->modx->prepare("SHOW COLUMNS IN {$this->modx->getTableName($class)}");
				$c->execute();
				while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
					$tableFields[$cl['Field']] = $cl['Field'];
				}
				foreach ($this->modx->getFields($class) as $field => $v) {
					if (in_array($field, $tableFields)) {
						unset($tableFields[$field]);
						$manager->alterField($class, $field);
					} else {
						$manager->addField($class, $field);
					}
				}
				foreach ($tableFields as $field) {
					$manager->removeField($class, $field);
				}
				// 2. Operate with indexes
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
				foreach ($indexes as $name => $values) {
					sort($values);
					$indexes[$name] = implode(':', $values);
				}
				$map = $this->modx->getIndexMeta($class);
				// Remove old indexes
				foreach ($indexes as $key => $index) {
					if (!isset($map[$key])) {
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
	 * @param object $package The grvPackage xPDOSimpleObject
	 */
	public function generateSchema($package)
	{
		// Templates
		$packageTpl = '<model package="{package_key}" baseClass="{base_class}" platform="{platform}" defaultEngine="{default_engine}" phpdoc-package="{phpdoc_package}" phpdoc-subpackage="{phpdoc_subpackage}" version="1.1">';
		$objectTpl = '<object class="{class}" table="{table_name}" extends="{extends}">';
		$fieldTpl = '<field key="{column_name}" dbtype="{dbtype}" precision="{precision}" phptype="{phptype}" null="{allownull}" default="{default}"/>';
		$indexTpl = '<index alias="{column_name}" name="{column_name}" primary="false" unique="false" type="{index}">
					<column key="{column_name}" length="" collation="A" null="false"/>
					</index>';
		$relTpl = '<{relation_type} alias="{alias}" class="{class}" local="{local}" foreign="{foreign}" cardinality="{cardinality}" owner="{owner}"/>';

		// Start the schema
		$this->logMessages[] = "Building schema...";
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
						// Populate the field row
						$xmlSchema .= $this->replaceValues($field->toArray(), $fieldTpl);

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

	}

	/**
	 * Replace placeholders with values
	 * 
	 */
	private function replaceValues($objectArray, $stringTpl)
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
	private function rrmdir($src) {
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

return 'GrvBuildPackageProcessor';
