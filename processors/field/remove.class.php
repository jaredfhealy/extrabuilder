<?php

/**
 * Remove a Field.
 *
 * @package extrabuilder
 * @subpackage processors/field
 */
class ExtrabuilderFieldRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'ebField';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.field';
}

return 'ExtrabuilderFieldRemoveProcessor';
