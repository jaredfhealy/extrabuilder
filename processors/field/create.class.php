<?php

/**
 * Create a Field
 *
 * @package grv
 * @subpackage processors.object
 */
class GrvFieldCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvField';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.field';
	
}
return 'GrvFieldCreateProcessor';