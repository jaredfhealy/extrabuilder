<?php

//v3 only
namespace ExtraBuilder\Processors\ebPackage;

use \MODX\Revolution\Processors\Processor;
use SimpleXMLElement;
//v3 only

class PkgImportSchema extends Processor {
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
		$bodyArr = json_decode(file_get_contents('php://input'), true);
        $schema = '';
        $action = 'update';

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
        }

        if (!empty($realSchemaPath)) {
            // If this is a file
            if (is_file($realSchemaPath) && mime_content_type($realSchemaPath) === 'text/xml') {
                // Try and read the file
                $schema = new SimpleXMLElement($realSchemaPath, 0, true);
            } else {
                return $this->failure('Incorrect file type: ' . MODX_CORE_PATH . 'components/' . $bodyArr['schema_file_path']);
            }
        } else if (!empty($schemaXml)) {
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
                $action = 'create';
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
                    $object = $this->modx->newObject($objectClass);
                    $objArr = $schemaObject->attributes();

                    // Check for an existing field, if we have a package ID or create new
                    if ($action === 'update') {
                        $object = $this->modx->getObject($objectClass, [
                            'package' => $package->get('id'),
                            'class' => $objArr['class']->__toString(),
                        ]);
                    } else {
                        // Create a new object
                        $object = $this->modx->newObject($objectClass);
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

                            // Check for an existing field, if we have a object ID or create new
                            if ($action === 'update') {
                                $field = $this->modx->getObject($fieldClass, [
                                    'object' => $object->get('id'),
                                    'column_name' => $objArr['key']->__toString(),
                                ]);
                            } else {
                                // Create a new fields
                                $field = $this->modx->newObject($fieldClass);
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
                        unset($childFields); // save memory
                    }

                    // Now add child composite relationship entries
                    if (isset($schemaObject->composite)) {
                        $childRels = [];
                        foreach ($schemaObject->composite as $schemaRel) {
                            // Get the attributes
                            $objArr = $schemaRel->attributes();

                            // Check for an existing field, if we have a object ID or create new
                            if ($action === 'update') {
                                $rel = $this->modx->getObject($relClass, [
                                    'object' => $object->get('id'),
                                    'alias' => $objArr['alias']->__toString(),
                                    'relation_type' => 'composite',
                                ]);
                            } else {
                                // Create a new fields
                                $rel = $this->modx->newObject($relClass);
                                $rel->set('relation_type', 'composite');
                            }

                            // Set values from the xml
                            $this->setValueFromAttribute($rel, 'alias', $objArr, 'alias');
                            $this->setValueFromAttribute($rel, 'class', $objArr, 'class');
                            $this->setValueFromAttribute($rel, 'local', $objArr, 'local');
                            $this->setValueFromAttribute($rel, 'foreign', $objArr, 'foreign');
                            $this->setValueFromAttribute($rel, 'cardinality', $objArr, 'cardinality');
                            $this->setValueFromAttribute($rel, 'owner', $objArr, 'owner');
                            $childRels[] = $rel;
                        }

                        // Add many fields to the object
                        $object->addMany($childRels);
                        unset($childRels); // save memory
                    }

                    // Now add child aggregate relationship entries
                    if (isset($schemaObject->aggregate)) {
                        $childRels = [];
                        foreach ($schemaObject->aggregate as $schemaRel) {
                            // Get the attributes
                            $objArr = $schemaRel->attributes();

                            // Check for an existing field, if we have a object ID or create new
                            if ($action === 'update') {
                                $rel = $this->modx->getObject($relClass, [
                                    'object' => $object->get('id'),
                                    'alias' => $objArr['alias']->__toString(),
                                    'relation_type' => 'aggregate',
                                ]);
                            } else {
                                // Create a new fields
                                $rel = $this->modx->newObject($relClass);
                                $rel->set('relation_type', 'aggregate');
                            }

                            // Set values from the xml
                            $this->setValueFromAttribute($rel, 'alias', $objArr, 'alias');
                            $this->setValueFromAttribute($rel, 'class', $objArr, 'class');
                            $this->setValueFromAttribute($rel, 'local', $objArr, 'local');
                            $this->setValueFromAttribute($rel, 'foreign', $objArr, 'foreign');
                            $this->setValueFromAttribute($rel, 'cardinality', $objArr, 'cardinality');
                            $this->setValueFromAttribute($rel, 'owner', $objArr, 'owner');
                            $childRels[] = $rel;
                        }

                        // Add many fields to the object
                        $object->addMany($childRels);
                        unset($childRels); // save memory
                    }

                    // Add the object with child fields to the child object array
                    $childObjects[] = $object;

                    // Add many child objects to the parent package
                    $package->addMany($childObjects);
                }
            }

            // Save the package
            if ($package->save()) {
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
    private function setValueFromAttribute($record, $fieldName, $attributes, $key)
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