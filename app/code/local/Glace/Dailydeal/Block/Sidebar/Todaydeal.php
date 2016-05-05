<?php

class Glace_Dailydeal_Block_Sidebar_Todaydeal extends Mage_Core_Block_Template
{

    public function getDeals()
    {
        $store_id = Mage::app()->getStore()->getId();
        
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection = Glace_Dailydeal_Model_Dailydeal::getModel()->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')))
                ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                ->addProductStatusFilter($store_id)
                ->getConfigSortBy();
        
        $collection->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );

        $collection->getSelect()->where("stock.is_in_stock = " . Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

        return $collection;
        
    }
    
    
}