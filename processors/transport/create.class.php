<?php

/**
 * Create a Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderTransportCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.transport';
	
}
return 'ExtrabuilderTransportCreateProcessor';