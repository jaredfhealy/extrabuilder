<?php

/**
 * Import schema
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvImportSchemaProcessor extends modObjectCreateProcessor
{
	public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.package';

	/**
	 * Parsed XML Document
	 */
	public $xmlDoc;

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		$bodyArr = json_decode(file_get_contents('php://input'), true);
		$schema = '';

		$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($bodyArr, true));
		$schemaFilePath = '';
		if ($bodyArr['schema_file_path']) {
			$schemaFilePath = realpath(MODX_CORE_PATH . 'components/' . $bodyArr['schema_file_path']);
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Schema path: $schemaFilePath, type: ".gettype($schemaFilePath));
		}
		
		if (!empty($schemaFilePath)) {
			// If this is a file
			if (is_file($schemaFilePath) && mime_content_type($schemaFilePath) === 'text/xml') {
				// Try and read the file
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Parse the file.");
				$schema = new SimpleXMLElement($schemaFilePath, 0, true);
			}
			else {
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, "This is not a file.");
			}
		}
		else if (!empty($bodyArr['schema_xml'])) {
			// Just use the xml passed in
			$schema = new SimpleXMLElement($bodyArr['schema_xml']);
		}
		
		// Parse the model first
		if (isset($schema[0])) {
			// Get the xml attributes
			$modelArr = $schema[0]->attributes();

			// Check for an existing package or start a new entry
			$package = $this->modx->getObject('grvPackage', ['package_key' => $modelArr['package']->__toString()]);
			if (!$package) {
				$package = $this->modx->newObject('grvPackage');
			}

			// Set values from the xml
			$this->setValueFromAttribute($package, 'display', $modelArr, 'package');
			$this->setValueFromAttribute($package, 'package_key', $modelArr, 'package');
			$this->setValueFromAttribute($package, 'base_class', $modelArr, 'baseClass');
			$this->setValueFromAttribute($package, 'platform', $modelArr, 'platform');
			$this->setValueFromAttribute($package, 'default_engine', $modelArr, 'defaultEngine');
			$this->setValueFromAttribute($package, 'phpdoc_package', $modelArr, 'phpdoc-package');
			$this->setValueFromAttribute($package, 'phpdoc_subpackage', $modelArr, 'phpdoc-subpackage');
			$this->setValueFromAttribute($package, 'version', $modelArr, 'version');

			// Now add the object entries
			if (isset($schema->object)) {
				$childObjects = [];
				foreach ($schema->object as $schemaObject) {
					// Get the attributes
					$object = $this->modx->newObject('grvObject');
					$objArr = $schemaObject->attributes();

					// Check for an existing field, if we have a package ID or create new
					if ($package->get('id')) {
						$object = $this->modx->getObject('grvObject', [
							'package' => $package->get('id'),
							'class' => $objArr['class']->__toString()
						]);
						if (!$object) {
							$object = $this->modx->newObject('grvObject');
						}
					}

					// Set values from the xml
					$this->setValueFromAttribute($object, 'class', $objArr, 'class');
					$this->setValueFromAttribute($object, 'table_name', $objArr, 'table');
					$this->setValueFromAttribute($object, 'extends', $objArr, 'extends');

					// Now add child field entries
					if (isset($schemaObject->field)) {
						$childFields = [];
						foreach ($schemaObject->field as $schemaField) {
							// Get the attributes
							$objArr = $schemaField->attributes();
							
							// Check for an existing field, if we have a package ID or create new
							if ($object->get('id')) {
								$field = $this->modx->getObject('grvField', [
									'object' => $object->get('id'),
									'column_name' => $objArr['key']->__toString()
								]);
								if (!$field) {
									$field = $this->modx->newObject('grvField');
								}
							}

							// Set values from the xml
							$this->setValueFromAttribute($field, 'column_name', $objArr, 'key');
							$this->setValueFromAttribute($field, 'dbtype', $objArr, 'dbtype');
							$this->setValueFromAttribute($field, 'precision', $objArr, 'precision');
							$this->setValueFromAttribute($field, 'phptype', $objArr, 'phptype');
							$this->setValueFromAttribute($field, 'allownull', $objArr, 'null');
							$this->setValueFromAttribute($field, 'default', $objArr, 'default');
							$childFields[] = $field;
						}

						// Add many fields to the object
						$object->addMany($childFields);
					}

					// Add the object with child fields to the child object array
					$childObjects[] = $object;

					// Add many child objects to the parent package
					$package->addMany($childObjects);
				}
			}

			// Save the package
			$package->save();
		}
		else {
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Unable to parse schema");
		}
	}
	
	/**
	 * Get an attribute value if it exists from the SimpleXMLElement::attributes
	 * 
	 * @param object $record xPDOSimpleObject record
	 * @param string $fieldName The field name to set on the record
	 * @param object $attributes SimpleXMLElement::attributes object
	 * @param string $key The attribute name to retrieve the value from
	 */
	private function setValueFromAttribute($record, $fieldName, $attributes, $key)
	{
		// Check if the attribute exists first
		if ($attributes[$key]) {
			$record->set($fieldName, $attributes[$key]->__toString());
		}
	}
}
return 'GrvImportSchemaProcessor';