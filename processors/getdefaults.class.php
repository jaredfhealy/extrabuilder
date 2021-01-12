<?php

/**
 * Import schema
 *
 * @package grv
 * @subpackage processors
 */
class GrvGetDefaultsProcessor extends modProcessor
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
			'grvPackage' => $this->getFieldDefaults('grvPackage'),
			'grvObject' => $this->getFieldDefaults('grvObject'),
			'grvField' => $this->getFieldDefaults('grvField'),
			'grvRel' => $this->getFieldDefaults('grvRel'),
			'grvTransport' => $this->getFieldDefaults('grvTransport')
		];

		// Set the response
		return $this->success('', $objectFields);
	}

	// Get fields and remove "null"
	/**
	 * Get the field defaults and remove 'null'
	 */
	public function getFieldDefaults($class) 
	{
		// Use the modx function
		$fields = $this->modx->getFields($class);

		// Loop through values
		foreach ($fields as $field => $value) {
			if ($value === NULL) {
				// Pass an empty string instead
				$fields[$field] = "";
			}
		}

		// Return the JSON string encoded defaults for this class
		return json_encode($fields);
	}
}
return 'GrvGetDefaultsProcessor';