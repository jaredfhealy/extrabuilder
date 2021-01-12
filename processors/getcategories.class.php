<?php

/**
 * Get list Categories
 *
 * @package grv
 * @subpackage processors
 */
class GrvCategoryGetListProcessor extends modObjectGetListProcessor 
{
    public $classKey = 'modCategory';
    public $languageTopics = array('grv:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'mod.category';

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
return 'GrvCategoryGetListProcessor';