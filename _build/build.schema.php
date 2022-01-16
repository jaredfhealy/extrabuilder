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

// Initialize MODX and the manager context, load classes, etc.
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

// Get the manager and generator objects/functions
$manager = $modx->getManager();
$generator = $manager->getGenerator();

/// Define the paths needed
//{base_path}/core/components/
$projectRootDir = MODX_BASE_PATH . 'core/components/';

//{base_path}/core/components/ExtraBuilder
$corePath = $projectRootDir . 'ExtraBuilder/';

//{base_path}/core/components/ExtraBuilder/schema/extrabuilder.mysql.schema.xml
$schemaFile = $corePath . "schema/extrabuilder.mysql.schema.xml";

/**
 * Build schema files: Options available to pass through
 * 
 * @param int    $update     Indicates if existing class files should be updated; 0=no,
 *                           1=update platform classes, 2=update all classes.
 * @param int    $regenerate Indicates if existing class files should be regenerated;
 *                           0=no, 1=regenerate platform classes, 2=regenerate all classes.
 */
$generator->parseSchema($schemaFile,
	$corePath . 'src/',
	[
		"compile" => 0,
		"update" => 2,
		"regenerate" => 2,
		"namespacePrefix" => "ExtraBuilder\\"
	]
);