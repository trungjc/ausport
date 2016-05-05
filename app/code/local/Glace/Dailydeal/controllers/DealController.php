<?php

class Glace_Dailydeal_DealController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @deprecated since version 2.1.4
     * Some websites use https, they don't accept http => not return html
     */
    public function ajaxdealAction()
    {
        $store_id = Mage::app()->getStore()->getId();
        
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $dayselect = $this->getRequest()->getParam('currenttime');
        $Y = date('Y', Mage::getModel('core/date')->timestamp(time()));
        $startday = implode(array($dayselect, ' 00:00:00'));
        $startdaytime = strtotime($startday);
        $endday = implode(array($dayselect, ' 23:59:59'));
        $enddaytime = strtotime($endday);
        $deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view',array(array('like'=>'%'. Mage::app()->getStore()->getId() .'%'),array('like'=>'0')))
                ->addProductStatusFilter($store_id)
                ->getConfigSortBy();
        
        $deals->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );
        $deals->getSelect()->where("stock.is_in_stock = " . Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);
            
            $layout = Mage::getSingleton('core/layout');
            $block = $layout->createBlock('dailydeal/deal')
                    ->setData('deals', $deals)
                    ->setData('startdaytime', $startdaytime)
                    ->setData('enddaytime', $enddaytime)
                    ->setTemplate('glace_dailydeal/sidebar/calendar_product.phtml');
            $result = $block->renderView();
        echo $result;
        die;    // Because : have a exception "Header already sent"
    }

}