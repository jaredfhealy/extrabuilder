<?php

/**
 * Remove a Field.
 *
 * @package grv
 * @subpackage processors/field
 */
class GrvFieldRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'grvField';
    public $languageTopics = array('grv:default');
    public $objectType = 'grv.field';
}

return 'GrvFieldRemoveProcessor';