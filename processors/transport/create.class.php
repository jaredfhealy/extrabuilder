<?php

/**
 * Create a Transport
 *
 * @package grv
 * @subpackage processors.transport
 */
class GrvTransportCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvTransport';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.transport';
	
}
return 'GrvTransportCreateProcessor';