<?php

/**
 * Update a Object
 *
 * @package extrabuilder
 * @subpackage processors.object
 */
class ExtrabuilderObjectUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'ebObject';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.object';
}
return 'ExtrabuilderObjectUpdateProcessor';
