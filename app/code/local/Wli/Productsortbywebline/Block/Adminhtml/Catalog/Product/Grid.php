<?php
/**
 * Webline Magento
 * 
 * Adminhtml product grid block
 *
 * @category   Wli
 * @package    Wli_Productsortbywebline
 * @author     Webline Magento Team
 */
class Wli_Productsortbywebline_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
	public function setCollection($collection)
    {
       $store = $this->_getStore();
 
       	$moduleName = 'Wli_Productsortbywebline';
        if(Mage::helper('core')->isModuleEnabled($moduleName))
        { 
		    if ($store->getId() && !isset($this->_joinAttributes['wli_sort_order'])) {
		        $collection->joinAttribute(
		            'wli_sort_order',
		            'catalog_product/wli_sort_order',
		            'entity_id',
		            null,
		            'left',
		            $store->getId()
		        );
		    }
		    else {
		        $collection->addAttributeToSelect('wli_sort_order');
		    }
        }
	    parent::setCollection($collection);
    }
 
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $moduleName = 'Wli_Productsortbywebline'; 
        if(Mage::helper('core')->isModuleEnabled($moduleName))
        {              
		    $this->addColumnAfter('wli_sort_order',
		      	array(
		              'header'=> Mage::helper('catalog')->__('Sort Order'),
		              'width' => '50px',
		              'type'  => 'text',
		              'index' => 'wli_sort_order',
		      		  'align' => 'right',
		        	),
		   		'qty');
        }
	    return parent::_prepareColumns();
    }
 
    protected function getAttributeOptions($_attributeCode) {
 
        $options = array();
 
        $collection = Mage::getModel('eav/entity_attribute_option')->getCollection()
            ->setStoreFilter()
            ->join('attribute','attribute.attribute_id=main_table.attribute_id', 'attribute_code');
 
        foreach ($collection as $option) {
            if ($option->getAttributeCode() == $_attributeCode) {
                	$options[$option->getOptionId()] = $option->getValue();
                }
        }
        return $options;
    }
}