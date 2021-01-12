<?php

/**
 * Create a Relationship
 *
 * @package extrabuilder
 * @subpackage processors.rel
 */
class ExtrabuilderRelCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'ebRel';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.rel';
	
}
return 'ExtrabuilderRelCreateProcessor';