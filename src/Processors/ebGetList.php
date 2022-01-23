<?php

//v3 only
namespace ExtraBuilder\Processors;
use ExtraBuilder\Extrabuilder;
use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\Model\modCateogry;
use xPDO\Om\xPDOQuery;
use xPDO;
//v3 only

class ebGetList extends GetListProcessor {
    public $classKey;
    public $languageTopics = ['extrabuilder:default'];
    public $defaultSortField = 'id';
    public $objectType = 'extrabuilder.';
	public $isEbClass = true;

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
			if (strpos($className, '\\') === false) {
				// Set our class variable
				$this->classKey = $this->eb->model[$className]['class'];
				
				// Set object type
				$this->objectType .= $className;
			}
			else {
				// Generic query against classes not owned by EB
				$this->classKey = $className;
				$this->isEbClass = false;
				$this->objectType .= "general";
			}
			
			// Set the className public variable
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
		$searchFields = [];
		
		// If class is not ebPackage
		if ($this->isEbClass === true) {
			if (!empty($this->eb->model[$this->className]['parentField'])) {
				// Check for parentId
				$parentId = $this->getProperty('parentId') ?: 0;
				
				// Get the parent field storing the id
				$parentField = $this->eb->model[$this->className]['parentField'];

				// Add parent to the query
				$qc[$parentField.':='] = $parentId;
			}
		}
		
		// If we have a search
        $search = $this->getProperty('search');
		$search = $this->getProperty('type') === 'combo' ? $this->getProperty('query') : $search;
        if (!empty($search)) {
			// Handle the category query
			if (!$this->isEbClass && $this->className == 'MODX\\Revolution\\modCategory') {
				// Only return top level categories
				$qc['parent:='] = 0;
				$searchFields[] = 'category';
			}
			else {
				// Dynamically build our criteria
				$keyTemplate = "OR:%s:LIKE";
				$qc = ['id:=' => "'".$search."'"];

				// Loop through the fields for this class
				$fields = count($searchFields) > 0 ? $searchFields : $this->eb->model[$this->className]['searchFields'];
				foreach ($fields as $field) {
					// If this is not the ID field, add it to the search
					if ($field !== 'id')
						$qc[sprintf($keyTemplate, $field)] = '%'.$search.'%';
				}
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