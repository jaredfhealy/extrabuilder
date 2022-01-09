<?php

namespace ExtraBuilder\Model;

use ExtraBuilder\Model\ebPackage;
use MODX\Revolution\modX;
use xPDO\om\xPDO;

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

/// Define the paths needed
//{base_path}/core/components/
$projectRootDir = MODX_BASE_PATH . 'core/components/';

//{base_path}/core/components/ExtraBuilder
$corePath = $projectRootDir . 'ExtraBuilder/';

$package = $modx->newObject('ExtraBuilder\\Model\\ebPackage');
if ($package) {
	$modx->log(1, print_r($package->getGridModel(), true));
}