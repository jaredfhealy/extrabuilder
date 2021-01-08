<?php

/**
 * Get list Package
 *
 * @package grv
 * @subpackage processors.package
 */
class GrvPackageGetListProcessor extends modObjectGetListProcessor 
{
    public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'grv.package';

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
}
return 'GrvPackageGetListProcessor';