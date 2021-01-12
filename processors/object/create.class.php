<?php

/**
 * Create a Object
 *
 * @package extrabuilder
 * @subpackage processors.object
 */
class ExtrabuilderObjectCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'ebObject';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.object';
	
}
return 'ExtrabuilderObjectCreateProcessor';