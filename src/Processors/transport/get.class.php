<?php

/**
 * Get Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderTransportGetProcessor extends modObjectGetProcessor
{
    public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';
}

return 'ExtrabuilderTransportGetProcessor';
