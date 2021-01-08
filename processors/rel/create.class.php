<?php

/**
 * Create a Relationship
 *
 * @package grv
 * @subpackage processors.rel
 */
class GrvRelCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvRel';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.rel';
	
}
return 'GrvRelCreateProcessor';