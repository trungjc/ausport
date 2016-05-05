<?php
class Glace_Dailydeal_Block_Deal extends Mage_Catalog_Block_Product_View_Abstract
{
	public function _prepareLayout()
    { 
		$this->setTemplate('glace_dailydeal/symbolicdeal.phtml');
	//	return parent::_prepareLayout();
    }
    /**
     * Retrieve url for direct adding product to cart
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }

        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }

        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => false));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);

        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }
    //getcollection 1 mang cac deal kich hoat, sap xep theo tu tu kich hoat trc tu tren xuong
    //de dang chon dc deal dc uu tien
    public function getDeals($showtotal)
    {
		$tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
    	 	$currenttime = date('Y-m-d H:i:s',Mage::getModel('core/date')->timestamp(time()));    	
        	$deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
    												->addFieldToFilter('status','1')
    												->addFieldToFilter('featured','1')
    												->addFieldToFilter('start_date_time',array('to' => $currenttime))
 													->addFieldToFilter('end_date_time',array('from' => $currenttime))
 													->addFieldToFilter('sold_qty', array('lt' => 'deal_qty'))
    												->addAttributeToSort('dailydeal_id','ASC')
 													->addAttributeToSort('start_date_time','ASC');    
													$deals->getSelect()->joinLeft(      
	       array('stock'=>$tblCatalogStockItem),     
	       'stock.product_id = main_table.product_id',      
	       array('stock.qty', 'stock.is_in_stock')      
     	);						
		//$deals->getSelect()->where("stock.qty > 0");        
		$deals->getSelect()->where("stock.is_in_stock = 1");																							
		if ($deals->count() > 0)    	{
			//echo 'death';//die;						
    		return $deals;
		}
    	else {
    		$deals = Mage::getModel('dailydeal/dailydeal')->getCollection() 
    												->addFieldToFilter('status','1')   	
    												->addFieldToFilter('start_date_time',array('to' => $currenttime))
 													->addFieldToFilter('end_date_time',array('from' => $currenttime))
 													->addAttributeToSort('dailydeal_id','ASC')					
    												->addAttributeToSort('start_date_time','ASC');
													$deals->getSelect()->joinLeft(      
	       array('stock'=>$tblCatalogStockItem),     
	       'stock.product_id = main_table.product_id',      
	       array('stock.qty', 'stock.is_in_stock')      
     	);						
		//$deals->getSelect()->where("stock.qty > 0");        
		$deals->getSelect()->where("stock.is_in_stock = 1");
			return $deals;	
    	}
    }
}