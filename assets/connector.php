<?php

use MODX\Revolution\modX;
use xPDO\xPDO;

/**
 * ExtraBuilder Connector
 *
 * @var MODX\Revolution\modX $modx
 * 
 * @package ExtraBuilder
 */

// Define package name and rootDir
$packageKey = basename(dirname(__FILE__, 2)) === 'components' ? basename(dirname(__FILE__)) : basename(dirname(__FILE__, 2));

// Determine where we're at. Asset path possibilities
// Development: core/components/<key>/assets
// Prod:        assets/components/<key>/
$rootConfig = is_file(dirname(__FILE__, 4).'/config.core.php') ? dirname(__FILE__, 4).'/config.core.php' : dirname(__FILE__, 5).'/config.core.php';
$corePath = dirname($rootConfig);

if (is_file($rootConfig)) {
    // Include our config file
    require_once($rootConfig);

    // Get the full config
    require_once(MODX_CORE_PATH.MODX_CONFIG_KEY.'/config.inc.php');

	// Set the connector include constant
	define('MODX_CONNECTOR_INCLUDED', true);

    // Bring in the connector index
    require_once(MODX_CONNECTORS_PATH . 'index.php');

}

// If we now have a core path defined
if (defined('MODX_CORE_PATH')) {
	// Dynamic classname based on packageKey
    $service = $modx->services->has($packageKey) ? $modx->services->get($packageKey) : "";
	if ($service) {
		$serviceKey = $service->config['serviceKey'];
		$modx->$serviceKey =& $service;
	}
    $modx->lexicon->load("${packageKey}:default");

	if (!$modx->$serviceKey) {
		header("Content-Type: application/json; charset=UTF-8");
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		echo json_encode([
			'success' => false,
			'code' => 404,
			'message' => "Unable to load class: $packageKey"
		]);
		die();
	}

    /* handle request */
    $path = $modx->getOption('processorsPath', $modx->$serviceKey->config, $corePath . 'processors/');
    $modx->request->handleRequest(['processors_path' => $path]);
}
else {
	header("Content-Type: application/json; charset=UTF-8");
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	echo json_encode([
		'success' => false,
		'code' => 404,
		'message' => "Unable to load MODX"
	]);
	die();
}