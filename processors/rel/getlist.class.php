<?php

/**
 * Get list Relationship
 *
 * @package extrabuilder
 * @subpackage processors.package
 */
class ExtrabuilderRelGetListProcessor extends modObjectGetListProcessor 
{
    public $classKey = 'ebRel';
    public $languageTopics = array('extrabuilder:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'extrabuilder.rel';

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
return 'ExtrabuilderRelGetListProcessor';