<?php

/**
 * Remove a Transport
 *
 * @package grv
 * @subpackage processors/transport
 */
class GrvTransportRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'grvTransport';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.transport';
}

return 'GrvTransportRemoveProcessor';