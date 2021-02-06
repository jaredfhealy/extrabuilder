<?php

/**
 * Remove a Transport
 *
 * @package extrabuilder
 * @subpackage processors/transport
 */
class ExtrabuilderTransportRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';
}

return 'ExtrabuilderTransportRemoveProcessor';
