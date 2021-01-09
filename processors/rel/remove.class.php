<?php

/**
 * Remove an Relationship.
 *
 * @package grv
 * @subpackage processors/rel
 */
class GrvRelRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'grvRel';
    public $languageTopics = array('grv:default');
    public $objectType = 'grv.rel';
}

return 'GrvRelRemoveProcessor';