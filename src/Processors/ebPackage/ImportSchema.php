<?php

namespace ExtraBuilder\Processors\ebPackage;

use ExtraBuilder\Model\ebPackage;
use ExtraBuilder\Model\ebObject;
use ExtraBuilder\Model\ebField;
use ExtraBuilder\Model\ebRel;
use ExtraBuilder\Model\ebTransport;
use \MODX\Revolution\Processors\Processor;
use SimpleXMLElement;
use DOMDocument;

class ImportSchema extends Processor {
    public $languageTopics = ['extrabuilder:default'];
    public $objectType = 'extrabuilder.';

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb; 

	/** @var string $className */
	public $className = "";

    /**
	 * Override initialize
	 *
	 */
    public function initialize()
    {
		// Store a reference to our service class that was loaded in 'connector.php'
		$this->eb =& $this->modx->eb;
        
        return parent::initialize();
    }

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
        // Moving to ExtJS, we now get a form post instead of JSON
		$schemaXml = $this->getProperty('schema_xml');
		$schemaFilePath = $this->getProperty('schema_file_path');
        $schema = '';

		// Get class names as variables to handle classname format changes in 3.0
		$packageClass = $this->eb->getClass('ebPackage');
		$objectClass = $this->eb->getClass('ebObject');
		$fieldClass = $this->eb->getClass('ebField');
		$relClass = $this->eb->getClass('ebRel');

		// If we have a schema file path, try that first
        if ($schemaFilePath) {
            $realSchemaPath = realpath(MODX_BASE_PATH . $schemaFilePath);
            if (!$realSchemaPath) {
                return $this->failure('Path Invalid: ' . MODX_BASE_PATH . $schemaFilePath);
            }
			// If this is a file
            else if (mime_content_type($realSchemaPath) === 'text/xml') {
                // Try and read the file
                $schema = new SimpleXMLElement($realSchemaPath, 0, true);
            } else {
                return $this->failure('Incorrect file type: ' . $realSchemaPath);
            }
        }
		else if (!empty($schemaXml)) {
            // Just use the xml passed in
            $schema = new SimpleXMLElement($schemaXml);
        }

        // Parse the model first
        if (isset($schema[0])) {
            // Get the xml attributes
            $modelArr = $schema[0]->attributes();

            // Check for an existing package or start a new entry
            $package = $this->modx->getObject($packageClass, ['package_key' => $modelArr['package']->__toString()]);
            if (!$package) {
                $package = $this->modx->newObject($packageClass);
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

			// Save the package and the ID
			$packageId = $package->save();

            // Now add the object entries
            if (isset($schema->object)) {
                $childObjects = [];
                foreach ($schema->object as $schemaObject) {
                    // Get the attributes
                    $object = $this->modx->newObject($objectClass);
                    $objArr = $schemaObject->attributes();

                    // Check for an existing field, if we have a package ID or create new
                    $object = $this->modx->getObject($objectClass, ['package' => $package->get('id'), 'class' => $objArr['class']->__toString()]);
                    if (!$object) {
                        // Create a new object
                        $object = $this->modx->newObject($objectClass, ['package' => $package->get('id')]);
						$this->setValueFromAttribute($object, 'class', $objArr, 'class');
                    }

                    // Set values from the xml
                    $this->setValueFromAttribute($object, 'table_name', $objArr, 'table');
                    $this->setValueFromAttribute($object, 'extends', $objArr, 'extends');

					// Save the object
					$object->save();

                    // Now add child field entries
                    if (isset($schemaObject->field)) {
                        // Loop through the fields
						$childFields = [];
                        foreach ($schemaObject->field as $schemaField) {
                            // Get the attributes
                            $objArr = $schemaField->attributes();

                            // Check for an existing field, if we have a object ID or create new
                            $field = $this->modx->getObject($fieldClass, ['object' => $object->get('id'), 'column_name' => $objArr['key']->__toString()]);
                            if (!$field) {
                                // Create a new fields
                                $field = $this->modx->newObject($fieldClass, ['object' => $object->get('id')]);
								$this->setValueFromAttribute($field, 'column_name', $objArr, 'key');
                            }

                            // Set values from the xml
                            $this->setValueFromAttribute($field, 'dbtype', $objArr, 'dbtype');
                            $this->setValueFromAttribute($field, 'precision', $objArr, 'precision');
                            $this->setValueFromAttribute($field, 'phptype', $objArr, 'phptype');
                            $this->setValueFromAttribute($field, 'allownull', $objArr, 'null');
                            $this->setValueFromAttribute($field, 'default', $objArr, 'default');

							// Save the field
							$field->save();
                        }
                    }

                    // Now add child composite relationship entries
                    if (isset($schemaObject->composite)) {
                        $childRels = [];
                        foreach ($schemaObject->composite as $schemaRel) {
                            // Get the attributes
                            $objArr = $schemaRel->attributes();

                            // Check for an existing field, if we have a object ID or create new
                            $rel = $this->modx->getObject($relClass, [
                                    'object' => $object->get('id'),
                                    'alias' => $objArr['alias']->__toString(),
                                    'relation_type' => 'composite'
							]);
                            if (!$rel) {
                                // Create a new fields
                                $rel = $this->modx->newObject($relClass, ['object' => $object->get('id')]);
								$rel->set('relation_type', 'composite');
								$this->setValueFromAttribute($rel, 'alias', $objArr, 'alias');
                            }

                            // Set values from the xml
                            $this->setValueFromAttribute($rel, 'class', $objArr, 'class');
                            $this->setValueFromAttribute($rel, 'local', $objArr, 'local');
                            $this->setValueFromAttribute($rel, 'foreign', $objArr, 'foreign');
                            $this->setValueFromAttribute($rel, 'cardinality', $objArr, 'cardinality');
                            $this->setValueFromAttribute($rel, 'owner', $objArr, 'owner');

							// Save the composite
							$rel->save();
                        }
                    }

                    // Now add child aggregate relationship entries
                    if (isset($schemaObject->aggregate)) {
                        $childRels = [];
                        foreach ($schemaObject->aggregate as $schemaRel) {
                            // Get the attributes
                            $objArr = $schemaRel->attributes();

                            // Check for an existing field, if we have a object ID or create new
                            $rel = $this->modx->getObject($relClass, [
								'object' => $object->get('id'),
								'alias' => $objArr['alias']->__toString(),
								'relation_type' => 'aggregate',
							]);
                            if(!$rel) {
                                // Create a new fields
                                $rel = $this->modx->newObject($relClass, ['object' => $object->get('id')]);
								$rel->set('relation_type', 'aggregate');
								$this->setValueFromAttribute($rel, 'alias', $objArr, 'alias');
                            }

                            // Set values from the xml
                            $this->setValueFromAttribute($rel, 'class', $objArr, 'class');
                            $this->setValueFromAttribute($rel, 'local', $objArr, 'local');
                            $this->setValueFromAttribute($rel, 'foreign', $objArr, 'foreign');
                            $this->setValueFromAttribute($rel, 'cardinality', $objArr, 'cardinality');
                            $this->setValueFromAttribute($rel, 'owner', $objArr, 'owner');

							// Save the aggregate
							$rel->save();
                        }
                    }

					// Now update fields with an index value
					$indexFields = [];
                    if (isset($schemaObject->index)) {
                        // Loop through the index entries
                        foreach ($schemaObject->index as $index) {
                            // Get the attributes
                            $objArr = $index->attributes();

                            // Get the field this index is tied to
                            $field = $this->modx->getObject($fieldClass, [
								'object' => $object->get('id'),
								'column_name' => $index['alias']->__toString(),
							]);

                            // Set the type into the field index column
							if ($field && $index->count() == 1) {
								// Get the type, primary and unique values
								$type = $index['type']->__toString();
								$primary = $index['primary']->__toString();
								$unique = $index['unique']->__toString();
								$indexAttr = "primary=\"$primary\" unique=\"$unique\"";

								// Override the type based on options
								if ($primary == 'false' && $unique == 'true') {
									$type = 'BTREE2';
								}
								else if ($primary == 'true' && $unique == 'true') {
									$type = 'BTREE3';
								}

								// Set the field values
								$field->set('index', $type);
								$field->set('index_attributes', $indexAttr);

								// Save the changes
								$field->save();

								// Store this to the fields list with an index
								$indexFields[] = $field->get('id');
							}
							else {
								// This is a PRIMARY index or index with child columns
								// Just add to the object raw xml
								$dom = new DOMDocument('1.0');
								$dom->preserveWhiteSpace = false;
								$dom->formatOutput = true;
								if ($dom->loadXML($index->asXML())) {
									$object->set('raw_xml', $dom->saveXML($dom->documentElement));
									$object->save();
								}
								unset($dom);
							}
                        }
                    }

					/**
					 * Make sure all fields for this object that didn't have an index,
					 * have their index value cleared out
					 */
					// Build a query to the fields for this object
					$query = $this->modx->newQuery($fieldClass);
					$qc = [
						'object:=' => $object->get('id')
					];
					if (count($indexFields) > 0) {
						$qc['id:NOT IN'] = $indexFields;
					}
					$query->where($qc);

					// Now get the fields
					$fields = $this->modx->getCollection($fieldClass, $query);
					if ($fields) {
						foreach ($fields as $field) {
							// Clear the value
							$field->set('index', '');
							$field->save();
						}
					}

					// Check if there is a validation block
					if (isset($schemaObject->validation)) {
						// Get any existing xml value
						$xml = $object->get('raw_xml');
						foreach ($schemaObject->validation as $validation) {
							// Add to the xml string
							$dom = new DOMDocument('1.0');
							$dom->preserveWhiteSpace = false;
							$dom->formatOutput = true;
							if ($dom->loadXML($validation->asXML())) {
								if ($xml)
									$xml .= PHP_EOL;
								$xml .= $dom->saveXML($dom->documentElement);
							}
						}

						// If xml has changed
						if ($xml !== $object->get('raw_xml')) {
							// Store the new XML value
							$object->set('raw_xml', $xml);
							$object->save();
						}
					}
                }
            }

            // If we have a package ID
            if ($packageId) {
                return $this->success('Package created from schema');
            }
        } else {
            return $this->failure('Unable to parse the schema');
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
    private function setValueFromAttribute(&$record, $fieldName, $attributes, $key)
    {
        // Check if the attribute exists first
        if ($attributes[$key]) {
            $record->set($fieldName, $attributes[$key]->__toString());
        }
    }

	public function getLanguageTopics()
    {
        return $this->languageTopics;
    }
}