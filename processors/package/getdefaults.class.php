<?php

/**
 * Import schema
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvGetDefaultsProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.package';

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Build the return array
		$objectFields = [
			'grvPackage' => json_encode($this->modx->getFields('grvPackage')),
			'grvObject' => json_encode($this->modx->getFields('grvObject')),
			'grvField' => json_encode($this->modx->getFields('grvField')),
			'grvRel' => json_encode($this->modx->getFields('grvRel'))
		];

		// Set the response
		return $this->success('', $objectFields);
	}
}
return 'GrvGetDefaultsProcessor';