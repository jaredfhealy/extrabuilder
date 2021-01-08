<?php

/**
 * Get list Fields
 *
 * @package grv
 * @subpackage processors.field
 */
class GrvFieldGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'grvField';
    public $languageTopics = array('grv:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'grv.field';

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
return 'GrvFieldGetListProcessor';