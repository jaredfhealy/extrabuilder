<?php

/**
 * Update a Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderTransportUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';
}
return 'ExtrabuilderTransportUpdateProcessor';
