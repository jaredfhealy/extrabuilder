<?php

/**
 * Update a Transport
 *
 * @package grv
 * @subpackage processors.transport
 */
class GrvTransportUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'grvTransport';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.transport';
}
return 'GrvTransportUpdateProcessor';