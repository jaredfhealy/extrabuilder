<?php

//v3 only
namespace ExtraBuilder\Processors;

use \MODX\Revolution\Processors\Model\CreateProcessor;
//v3 only

class ebCreate extends CreateProcessor {
    public $classKey;
    public $languageTopics = ['extrabuilder:default'];
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
		// Return error if we don't have our service class
		if (!$this->modx->eb) {
			return $this->failure('Service Class is not defined. Validate connector.php is correct.');
		}
		else {
			// Store a reference to our service class that was loaded in 'connector.php'
			$this->eb =& $this->modx->eb;
		}

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

	/**
     * Override in your derivative class to do functionality before the fields are set on the object
	 * 
	 * Enforce package name for v2 to include <mycomponent>.v2.model and in lowercase
     *
     * @return boolean
     */
    public function beforeSet()
    {
		// If classKey is ebPackage
		if ($this->classKey === 'ebPackage' && !$this->eb->isV3) {
			// Make sure it's lowercase
			$package = $this->getProperty('package_key');
			$package = strtolower($package);
			if (strpos($package, '.v2.model') === false) {
				if (strpos($package, '.') === false) {
					$package .= '.v2.model';
				}
				else {
					// Get the beginning
					$package = explode('.', $package)[0] . '.v2.model';
				}
			}

			// Override the property
			$this->setProperty('package_key', $package);
		}
        return !$this->hasErrors();
    }
}