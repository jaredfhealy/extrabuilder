<?php

/**
 * Update a Package
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvPackageUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.package';
}
return 'GrvPackageUpdateProcessor';