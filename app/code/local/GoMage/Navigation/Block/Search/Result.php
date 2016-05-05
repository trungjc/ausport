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
 * @since        Class available since Release 1.0
 */

class GoMage_Navigation_Block_Search_Result extends Mage_CatalogSearch_Block_Result
{
    public function setListCollection() {
        $this->getListBlock()
           ->setCollection($this->_getProductCollection());

       return $this;
    }
    
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
        	
        	if ( Mage::helper('gomage_navigation')->isEnterprise() )
        	{
        		$this->_productCollection = Mage::getSingleton('enterprise_search/search_layer')->getProductCollection();	
        	}
        	else 
        	{
        		$this->_productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();
        	}
        }

        return $this->_productCollection;
    }
        
}