<?php

/**
 * Create a Object
 *
 * @package grv
 * @subpackage processors.object
 */
class GrvObjectCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvObject';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.object';
	
}
return 'GrvObjectCreateProcessor';