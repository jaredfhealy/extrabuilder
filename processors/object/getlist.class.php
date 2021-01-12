<?php

/**
 * Get list Object
 *
 * @package extrabuilder
 * @subpackage processors.object
 */
class ExtrabuilderObjectGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'ebObject';
    public $languageTopics = array('extrabuilder:default');
    public $defaultSortField = 'sortorder';
    public $defaultSortDirection = 'ASC';
	public $objectType = 'extrabuilder.object';

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
return 'ExtrabuilderObjectGetListProcessor';