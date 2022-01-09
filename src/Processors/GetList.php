<?php

namespace ExtraBuilder\Processors;
use ExtraBuilder\Extrabuilder;
use \MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor {
    public $classKey;
    public $languageTopics = ['ExtraBuilder:default'];
    public $defaultSortField = 'id';
    public $objectType = 'extrabuilder.';

	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb;

	/** @var string $className */
	public $className = "";

	/**
	 * Override the initialize process to properly set public vars
	 * 
	 * Using a single GetList processor for all classes means we must
	 * have a class on the request so we can set and load it.
	 */
	public function initialize() 
	{
		// Store a reference to our service class that was loaded in 'connector.php'
		$this->eb =& $this->modx->eb;
		
		// Check for a passed in class
		$className = $this->getProperty('classKey');
		if (!$className) {
			return $this->failure("Unable to determine the correct class to query.");
		}
		else {
			// Set our class variable
			$this->classKey = $this->eb->model[$className]['class'];

			// Set object type
			$this->objectType .= $className;
			$this->className = $className;
		}

		// Return true from the parent
		return parent::initialize();
	}

    /**
     * Override the query if we have a listId
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        // Start a new query condition
		$qc = [];
		
		// If class is not ebPackage
		if ($this->className !== 'ebPackage') {
			// Check for parentId
			$parentId = $this->getProperty('parentId') ?: 0;
			
			// Get the parent field storing the id
			$parentField = $this->eb->model[$this->className]['parentField'];

			// Add parent to the query
			$qc[$parentField.':='] = $parentId;
		}
		
		// If we have a search
        $search = $this->getProperty('search');
        if (!empty($search)) {
			// Dynamically build our criteria
			$keyTemplate = "OR:%s:LIKE";
			$qc = ['id:=' => "'".$search."'"];

			// Loop through the fields for this class
			$fields = $this->eb->model[$this->className]['searchFields'];
			foreach ($fields as $field) {
				// If this is not the ID field, add it to the search
				if ($field !== 'id')
					$qc[sprintf($keyTemplate, $field)] = '%'.$search.'%';
			}
        }

		if (count($qc) > 0) {
			// Apply the criteria
            $c->where($qc);
		}

        // Return the modified query
        return $c;
    }
}