<?php

/**
 * Update a Field
 *
 * @package grv
 * @subpackage processors.field
 */
class GrvFieldUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'grvField';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.field';
}
return 'GrvFieldUpdateProcessor';