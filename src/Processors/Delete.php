<?php

namespace ExtraBuilder\Processors;

use \MODX\Revolution\Processors\Model\RemoveProcessor;

class Delete extends RemoveProcessor {
    public $classKey;
    public $languageTopics = ['ExtraBuilder:default'];
    public $objectType = 'extrabuilder.';

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb;

	/** @var string $className */
	public $className = "";

    /**
	 * Override initialize to determine the classKey from parameters
	 *
	 */
    public function initialize()
    {
		// Store a reference to our service class that was loaded in 'connector.php'
		$this->eb =& $this->modx->eb;

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
        
        return parent::initialize();
    }
}