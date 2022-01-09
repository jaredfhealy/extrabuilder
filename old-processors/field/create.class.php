<?php

/**
 * Create a Field
 *
 * @package extrabuilder
 * @subpackage processors.object
 */
class ExtrabuilderFieldCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'ebField';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.field';

}
return 'ExtrabuilderFieldCreateProcessor';
