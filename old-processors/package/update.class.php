<?php

/**
 * Update a Package
 *
 * @package extrabuilder
 * @subpackage processors.package
 */
class ExtrabuilderPackageUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'ebPackage';
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.package';
}
return 'ExtrabuilderPackageUpdateProcessor';
