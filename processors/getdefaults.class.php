<?php

/**
 * Import schema
 *
 * @package extrabuilder
 * @subpackage processors
 */
class ExtrabuilderGetDefaultsProcessor extends modProcessor
{
	public $classKey = 'ebPackage';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.package';

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Build the return array
		$objectFields = [
			'ebPackage' => $this->getFieldDefaults('ebPackage'),
			'ebObject' => $this->getFieldDefaults('ebObject'),
			'ebField' => $this->getFieldDefaults('ebField'),
			'ebRel' => $this->getFieldDefaults('ebRel'),
			'ebTransport' => $this->getFieldDefaults('ebTransport')
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
return 'ExtrabuilderGetDefaultsProcessor';