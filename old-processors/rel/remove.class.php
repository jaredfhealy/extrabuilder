<?php

/**
 * Remove an Relationship.
 *
 * @package extrabuilder
 * @subpackage processors/rel
 */
class ExtrabuilderRelRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'ebRel';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.rel';
}

return 'ExtrabuilderRelRemoveProcessor';
