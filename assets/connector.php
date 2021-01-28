<?php
/**
 * Grv Connector
 *
 * @package extrabuilder
 * 
 * @var modX $modx
 */

// Determine where we're at. Asset path possibilities
// Development: core/components/<key>/assets
// Prod:        assets/components/<key>/
$devFilePath = dirname(__FILE__, 5).'/config.core.php';
$prodFilePath = dirname(__FILE__, 4).'/config.core.php';
if (is_file($prodFilePath))
	require_once $prodFilePath;
else if(is_file($devFilePath)) {
	require_once $devFilePath;
}
if (defined('MODX_CORE_PATH')) {
	require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
	require_once MODX_CONNECTORS_PATH.'index.php';

	$corePath = $modx->getOption('extrabuilder.core_path', null, $modx->getOption('core_path').'components/extrabuilder/');
	require_once $corePath.'model/extrabuilder/extrabuilder.class.php';
	$modx->eb = new Extrabuilder($modx);
	$modx->lexicon->load('extrabuilder:default');

	/* handle request */
	$path = $modx->getOption('processorsPath', $modx->eb->config, $corePath.'processors/');
	$modx->request->handleRequest(array(
		'processors_path' => $path,
		'location' => '',
	));
}