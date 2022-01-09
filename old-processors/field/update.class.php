<?php

/**
 * Update a Field
 *
 * @package extrabuilder
 * @subpackage processors.field
 */
class ExtrabuilderFieldUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'ebField';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.field';
}
return 'ExtrabuilderFieldUpdateProcessor';
