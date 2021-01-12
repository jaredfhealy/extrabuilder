<?php

/**
 * Get Transport
 *
 * @package grv
 * @subpackage processors.transport
 */
class GrvTransportGetProcessor extends modObjectGetProcessor
{
    public $classKey = 'grvTransport';
    public $languageTopics = array('grv:default');
    public $objectType = 'grv.transport';
}

return 'GrvTransportGetProcessor';