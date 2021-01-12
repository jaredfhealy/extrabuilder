<?php

/**
 * Get list Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderTransportGetListProcessor extends modObjectGetListProcessor 
{
    public $classKey = 'ebTransport';
    public $languageTopics = array('extrabuilder:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'extrabuilder.transport';

	/**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
		// Handle any passed in query
		$query = $this->getProperty('query');
		if (!empty($query)) {
			$queryArray = json_decode($query, true);
			if (is_array($queryArray)) {
				$c->where($queryArray);
			}
		}
        return $c;
	}
	
	/**
     * Can be used to insert a row after iteration
     * @param array $list
     * @return array
     */
    public function afterIteration(array $list) {
		// Get all packages as an array
		$packages = $this->modx->getCollection('ebPackage');

		// Loop through and add columns for the Package display
		foreach ($list as $key => $row) {
			if ($row['package']) {
				if ($package = $this->getPackageById($row['package'], $packages)) {
					// Add package details to the array
					$list[$key]['package.display'] = $package['display'];
					$list[$key]['package.package_key'] = $package['package_key'];
				}
				else {
					$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Unable to fine package');
				}
			}
		}

        return $list;
	}
	
	/**
	 * Private function to get a single package from the array
	 * @param int $id The id of the package
	 * @param array $packages The array collection of packages
	 * @return array The specific package as an object
	 */
	private function getPackageById($id, $packages)
	{
		// Loop through the packages
		foreach ($packages as $package) {
			// If the id matches
			if ($package->get('id') === $id) {
				// Return the package
				return $package->toArray();
			}
		}
		return false;
	}
}
return 'ExtrabuilderTransportGetListProcessor';