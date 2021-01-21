<?php

/**
 * Get list Package
 *
 * @package extrabuilder
 * @subpackage processors.package
 */
class ExtrabuilderPackageGetListProcessor extends modObjectGetListProcessor 
{
    public $classKey = 'ebPackage';
    public $languageTopics = array('extrabuilder:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'extrabuilder.package';

	/**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
		// Check for a limit set for paging
		$limit = $this->getProperty('limit');
		if (empty($limit)) {
			$this->setProperty('limit', 0);
		}

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
}
return 'ExtrabuilderPackageGetListProcessor';