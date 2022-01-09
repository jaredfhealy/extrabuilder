<?php

/**
 * Remove an Object.
 *
 * @package extrabuilder
 * @subpackage processors/object
 */
class ExtrabuilderObjectRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'ebObject';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.object';
}

return 'ExtrabuilderObjectRemoveProcessor';
