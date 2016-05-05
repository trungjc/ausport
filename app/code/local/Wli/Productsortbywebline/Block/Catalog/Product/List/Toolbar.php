<?php
/**
 * Webline Magento
 * 
 * Catalog Product List Toolbar block
 *
 * @category   Wli
 * @package    Wli_Productsortbywebline
 * @author     Webline Magento Team
 */
class Wli_Productsortbywebline_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
	/**
	 * Override product collection
	 * wli_sort_order attribute to sort product collection 
	 */
	public function setCollection($collection)
    {
    	$this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        
        /* sort by WLI SORT ORDER Custom attribute */
        $this->_collection->setOrder('wli_sort_order', $this->getCurrentDirection());
        
        return $this;
    }
}