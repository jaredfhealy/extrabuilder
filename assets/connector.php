<?php
/**
 * Grv Connector
 *
 * @package grv
 * 
 * @var modX $modx
 */

// Determine where we're at. Asset path possibilities
// Development: core/components/<key>/assets
// Prod:        assets/components/<key>/
$devFilePath = dirname(__FILE__, 5).'/config.core.php';
$prodFilePath = dirname(__FILE__, 3).'/config.core.php';
if (is_file($devFilePath))
	require_once $devFilePath;
else if(is_file($prodFilePath)) {
	require_once $prodFilePath;
}
if (defined('MODX_CORE_PATH')) {
	require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
	require_once MODX_CONNECTORS_PATH.'index.php';

	$corePath = $modx->getOption('grv.core_path',null,$modx->getOption('core_path').'components/grv/');
	require_once $corePath.'model/grv/grv.class.php';
	$modx->grv = new Grv($modx);
	$modx->lexicon->load('grv:default');

	/* handle request */
	$path = $modx->getOption('processorsPath',$modx->grv->config,$corePath.'processors/');
	$modx->request->handleRequest(array(
		'processors_path' => $path,
		'location' => '',
	));
}