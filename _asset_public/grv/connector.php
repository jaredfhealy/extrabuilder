<?php
/**
 * Grv Connector
 *
 * @package grv
 * 
 * @var modX $modx
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
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