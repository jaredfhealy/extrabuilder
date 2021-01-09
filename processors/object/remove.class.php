<?php

/**
 * Remove an Object.
 *
 * @package grv
 * @subpackage processors/object
 */
class GrvObjectRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'grvObject';
    public $languageTopics = array('grv:default');
    public $objectType = 'grv.object';
}

return 'GrvObjectRemoveProcessor';