<?php

/**
 * Simple inline build script without any class structure.
 * The purpose of this script is to allow rebuilding the
 * package manager schema and database tables externally.
 * 
 * Otherwise, if attempted through the package manager,
 * it breaks since the schema files change or are deleted
 * after they are loaded in MODX.
 * 
 * Access to execute this file is done through the Manager.
 * "Enable Rebuild Myself" adds a /_build directory in the
 * publicly accessible assets/components/grv/ with a
 * dynamically generated key which must be present as a query
 * parameter.
 */

$key = json_decode(file_get_contents(__DIR__.'/buildauthkey.json'), true)['key'];
$keyParam = !empty($_REQUEST['authkey']) ? $_REQUEST['authkey'] : "missing";
$deleteClassFiles = !empty($_REQUEST['delete_class_files'] ? true : false);
$dropTables = !empty($_REQUEST['drop_tables'] ? true : false);

if ($key !== $keyParam) {
	// Return early
	die("Not authorized: Key: $key, Key Param: $keyParam");
}

// Include config and MODX
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

// Initialize MODX and the manager context, load classes, etc.
$modx= new modX();
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder','',false, true);
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

// Get the manager and generator objects/functions
$manager= $modx->getManager();
$generator= $manager->getGenerator();

// Package key
$packageKey = 'grv';

// Define the needed paths/directories
$packageBasePath = MODX_CORE_PATH."components/$packageKey/";
$schemaPath = $packageBasePath."model/schema/";
$schemaFilePath = $schemaPath."$packageKey.mysql.schema.xml";
$classPath = $packageBasePath."model/$packageKey/";
$assetsPath = MODX_BASE_PATH."assets/components/$packageKey/";

// Make sure the directories are created
if(!is_dir($packageBasePath)){
	mkdir($packageBasePath, 0775, true);
}
if(!is_dir($schemaPath)){
	mkdir($schemaPath, 0775, true);
}
if(!is_dir($classPath)){
	mkdir($classPath, 0775, true);
}

// Define the schema file
/*if (file_put_contents($schemaFilePath, $xmlFormatted)) {
	// Written successfully
	$output .= "<p>XML Schema written successfully...</p>";
	$output .= "<pre>".htmlspecialchars($xmlFormatted)."</pre>";
}*/

// Begin the build script
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

// Initialize the manager and set log levels
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder','',false, true);
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('HTML');

// Set the sources array
$sources = array(
	'root' => MODX_BASE_PATH ,
	'core' => $packageBasePath,
	'model' => $packageBasePath.'model/',
	'assets' => $assetsPath,
	'schema' => $schemaPath,
);

// Get the manager and generator
$manager= $modx->getManager();
$generator= $manager->getGenerator();

if ($deleteClassFiles) {
	// First delete the main schema directory and contents
	rrmdir($classPath.'mysql/');

	// Delete the metadata file
	unlink($classPath.'metadata.mysql.php');
}

// Handle tables
// Code from: https://github.com/bezumkin/modExtra/blob/master/_build/resolvers/tables.php
$modx->addPackage($packageKey, $sources['model']);
$objects = [];
$output = "<h3>Parsing Schema for Tables/Classes</h3>";
if (is_file($schemaFilePath)) {
	$schema = new SimpleXMLElement($schemaFilePath, 0, true);
	if (isset($schema->object)) {
		foreach ($schema->object as $obj) {
			// Store the classname to loop through
			$objects[] = (string)$obj['class'];
			
			if ($deleteClassFiles) {
				// Delete the associated files
				unlink($classPath.strtolower((string)$obj['class']).'.class.php');
			}
		}
	}
	unset($schema);
}

// Run the build
$generator->parseSchema($schemaFilePath, $sources['model']);
$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);
$output .= "<p>Schema Finished... Execution time: $totalTime</p>";

$output .= "<pre>";
foreach ($objects as $class) {
	$output .= "Checking class: $class";
	$table = $modx->getTableName($class);
	$sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
	$stmt = $modx->prepare($sql);
	$newTable = true;
	if ($stmt->execute() && $stmt->fetchAll()) {
		$newTable = false;
	}
	// If the table is just created
	if ($newTable) {
		$manager->createObjectContainer($class);
	}
	else {
		// If the table exists
		// 1. Operate with tables
		$output .= PHP_EOL.'Table exists, checking columns...';
		$tableFields = [];
		$c = $modx->prepare("SHOW COLUMNS IN {$modx->getTableName($class)}");
		$c->execute();
		while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
			$tableFields[$cl['Field']] = $cl['Field'];
		}
		foreach ($modx->getFields($class) as $field => $v) {
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
		$output .= PHP_EOL."Table exists, checking indexes...";
		$indexes = [];
		$c = $modx->prepare("SHOW INDEX FROM {$modx->getTableName($class)}");
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
		$map = $modx->getIndexMeta($class);
		// Remove old indexes
		foreach ($indexes as $key => $index) {
			if (!isset($map[$key])) {
				if ($manager->removeIndex($class, $key)) {
					$modx->log(modX::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
				}
			}
		}
		// Add or alter existing
		foreach ($map as $key => $index) {
			ksort($index['columns']);
			$index = implode(':', array_keys($index['columns']));
			if (!isset($indexes[$key])) {
				if ($manager->addIndex($class, $key)) {
					$modx->log(modX::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
				}
			} else {
				if ($index != $indexes[$key]) {
					if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
						$modx->log(modX::LOG_LEVEL_INFO,
							"Updated index \"{$key}\" of the table \"{$class}\""
						);
					}
				}
			}
		}
	}
}
$output .= "</pre><p>End table processing</p><pre>";

$output .="<h3>Creating Namespace</h3>";
$namespace = $modx->getObject('modNamespace', ['name' => $packageKey]);
if (!$namespace) {
	$namespace = $modx->newObject('modNamespace', [
		'path' => "{core_path}components/$packageKey/",
		'assets_path' => "{assets_path}components/$packageKey/"
	]);
	$namespace->set('name', $packageKey);
	if ($namespace->save()) {
		$output .= 'Namespace created successfully...';
	}
}
else {
	$output .= 'Namespace already exists...';
}

/**
 * Delete directory recursively
 * 
 */
function rrmdir($src) {
	$dir = opendir($src);
	if ($dir) {
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				$full = $src . '/' . $file;
				if ( is_dir($full) ) {
					rrmdir($full);
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

echo "<html><head><title>Build Results</title></head><body>$output</body></html>";