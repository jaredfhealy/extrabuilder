<?php

/**
 * Create a Package
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvPackageCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.package';
	
}
return 'GrvPackageCreateProcessor';