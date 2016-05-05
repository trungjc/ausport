<?php
/**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 4.0
 * @since        Class available since Release 3.0
 */

class GoMage_Navigation_Model_Enterprise_Resource_Collection extends Enterprise_Search_Model_Resource_Collection {
	
	public function getSearchedEntityIds() {
		return $this->_searchedEntityIds;
	}
	
	public function getSize() {
		if (is_null($this->_totalRecords)) {
			if (! $this->isLoaded()) {
				$pageSize = $this->_pageSize;
				$this->_pageSize = false;
                $this->getSelect()->distinct();
				$this->load();
				$this->_pageSize = $pageSize;
				$this->_storedPageSize = null;
			}
			
			$select = clone $this->getSelect();
			$select->reset(Zend_Db_Select::LIMIT_COUNT);
			$select->reset(Zend_Db_Select::LIMIT_OFFSET);
			$select->reset(Zend_Db_Select::GROUP);
			
			$connection = Mage::getSingleton('core/resource')->getConnection('read');
			$result = $connection->fetchAll(( string ) $select);
			$this->_totalRecords = count($result);
		}
		
		return parent::getSize();
	}

}
