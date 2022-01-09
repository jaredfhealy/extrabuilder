<?php
// Include config
@include(dirname(__DIR__,4) . '/config.core.php');

if (!defined('MODX_CORE_PATH')) {
	echo("Core path not defined".PHP_EOL);
	return;
}

// Include the autoloader
@require_once (MODX_CORE_PATH . "vendor/autoload.php");

/* Create an instance of the modX class */
$modx= new \MODX\Revolution\modX();
$modx->initialize('web');

// Set log level and target
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

// Get the manager and generator objects/functions
$manager = $modx->getManager();

// Package key
$packageKey = 'ExtraBuilder';

// Logging function
function lg ($msg) {
	global $modx;
	$modx->log(modX::LOG_LEVEL_INFO, $msg);
}

// Make sure the namespace exists first
$namespace = $modx->getObject('modNamespace', ['name' => $packageKey]);
if (!$namespace) {
    $namespace = $modx->newObject('modNamespace', [
        'path' => "{core_path}components/$packageKey/",
        'assets_path' => "{assets_path}components/$packageKey/",
    ]);
    $namespace->set('name', $packageKey);
    if ($namespace->save()) {
        lg('Namespace created successfully...');
    }
} else {
    lg('Namespace already exists...');
}

// Classes to loop through
$classes = [
	'ExtraBuilder\Model\ebPackage',
	'ExtraBuilder\Model\ebObject',
	'ExtraBuilder\Model\ebField',
	'ExtraBuilder\Model\ebRel',
	'ExtraBuilder\Model\ebTransport'
];

// Get the manager
$manager = $modx->getManager();

// Parse table objects and store the names
// Code from: https://github.com/bezumkin/modExtra/blob/master/_build/resolvers/tables.php
foreach ($classes as $class) {
	// Check if the class exists first
    lg("Checking class: $class");
	if (!class_exists($class)){
		lg("Class does NOT exist: $class");
		continue;
	}

	// If the class exists, get the table
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
        lg('Table exists, checking columns...');
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
        lg("Table exists, checking indexes...");
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
lg("End table processing");