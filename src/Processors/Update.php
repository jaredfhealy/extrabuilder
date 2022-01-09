<?php

namespace ExtraBuilder\Processors;

use \MODX\Revolution\Processors\Model\UpdateProcessor;
use xPDO\Om\xPDOQuery;

class Update extends UpdateProcessor {
    public $classKey;
    public $languageTopics = ['ExtraBuilder:default'];
    public $objectType = 'extrabuilder.';

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb;

	/** @var string $className */
	public $className = "";

    /**
     * Parse data to the properties array from the
     * payload data. Format from the connector is
     * Form Data with parameters below.
     * 
     * This is only needed for inline editing which sends
     * the data in a different format.
     * 
     * UpdateProcessor extends ModelProcessor extends Processor.
     * The data is not automatically translated into the object
     * in any of these classes, you must parse the JSON.
     * 
     * @property string action The full classname including namespace
     * @property string data The data row as a JSON string
     * @property string HTTP_MODAUTH The unique site id
     * 
     * @return object Parent initialize function
     */
    public function initialize()
    {
		// Store a reference to our service class that was loaded in 'connector.php'
		$this->eb =& $this->modx->eb;

		/**
		 * Handle inline editing which comes through as a data array
		 */
        $data = $this->getProperty('data');
        $id = $this->getProperty('id');
        if (empty($data) && empty($id)) {
            return $this->modx->lexicon('invalid_data');
        }
        else if ($data && !$id) {
            $data = $this->modx->fromJSON($data);
            if (empty($data)) {
                return $this->modx->lexicon('invalid_data');
            }
            $this->setProperties($data);
            $this->unsetProperty('data');
        }

		// If we don't have a classname yet, the update wasn't inline
		if (!$className) {
			// Check for a passed in class
			$className = $this->getProperty('classKey');
			if (!$className) {
				$this->failure("Unable to determine the correct class to query.");
				return;
			}
			else {
				// Set our class variable
				$this->classKey = $this->eb->model[$className]['class'];

				// Set object type
				$this->objectType .= $className;
				$this->className = $className;
				$this->unsetProperty('classKey');
			}
		}
        
        return parent::initialize();
    }
}