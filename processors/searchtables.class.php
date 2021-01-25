<?php

/**
 * Import schema
 *
 * @package extrabuilder
 * @subpackage processors
 */
class ExtrabuilderSearchTablesProcessor extends modProcessor
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
		// Default return
		$results = [];

		// Get the passed filter parameter
		$filter = $this->getProperty('filter');
		
		// If we have a filter
		if ($filter) {
			// Query for tables
			$filter = $this->modx->quote("%$filter%");
			$sql = "SHOW TABLES LIKE $filter";
			$query = $this->modx->query($sql);
			while ($data = $query->fetchAll(PDO::FETCH_COLUMN)) {
				// Log the data object to see
				foreach ($data as $table) {
					$results[] = $table;
				}
			}
		}

		// Set the response
		return $this->success('', $results);
	}
}
return 'ExtrabuilderSearchTablesProcessor';