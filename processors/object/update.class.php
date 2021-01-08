<?php

/**
 * Update a Object
 *
 * @package grv
 * @subpackage processors.object
 */
class GrvObjectUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'grvObject';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.object';
}
return 'GrvObjectUpdateProcessor';