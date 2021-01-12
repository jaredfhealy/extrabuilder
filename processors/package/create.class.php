<?php

/**
 * Create a Package
 *
 * @package extrabuilder
 * @subpackage processors.package
 */
class ExtrabuilderPackageCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'ebPackage';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.package';
	
}
return 'ExtrabuilderPackageCreateProcessor';