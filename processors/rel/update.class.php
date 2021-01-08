<?php

/**
 * Update a Relationship
 *
 * @package grv
 * @subpackage processors.rel
 */
class GrvRelUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'grvRel';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.rel';
}
return 'GrvRelUpdateProcessor';