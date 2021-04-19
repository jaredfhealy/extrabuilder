<?php
/**
 * Connector
 *
 * @var modX $modx
 */

// Define package name and rootDir
$packageKey = basename(dirname(__FILE__, 2)) === 'components' ? basename(dirname(__FILE__)) : basename(dirname(__FILE__, 2));

// Determine where we're at. Asset path possibilities
// Development: core/components/<key>/assets
// Prod:        assets/components/<key>/
$rootPath = dirname(__FILE__, 4);
$rootConfig = $rootPath.'/config.core.php';

// In a prod install, this will be a valid file
if (is_file($rootConfig)) {
    require_once $rootConfig;
}
else if (basename($rootPath) == 'core') {
	// If we're in a dev install, set the core path manually
	define('MODX_CORE_PATH', $rootPath."/");
}

// If we now have a core path defined
if (defined('MODX_CORE_PATH')) {
	require_once MODX_CORE_PATH . 'config/config.inc.php';
	require_once MODX_CONNECTORS_PATH . 'index.php';

    $corePath = $modx->getOption("{$packageKey}.core_path", null, $modx->getOption('core_path') . "components/{$packageKey}/");
    require_once $corePath . "model/{$packageKey}/{$packageKey}.class.php";

    // Dynamic classname based on packageKey
    $className = ucfirst($packageKey);
    $modx->$packageKey = new $className($modx);
    $modx->lexicon->load('{$packageKey}:default');

    /* handle request */
    $path = $modx->getOption('processorsPath', $modx->$packageKey->config, $corePath . 'processors/');
    $modx->request->handleRequest(array(
        'processors_path' => $path,
        'location' => '',
    ));
}
