<?php

class Glace_Dailydeal_Block_Sidebar_Calendar extends Mage_Core_Block_Template
{

    public function getWeeklydeal()
    {
        $store_id = Mage::app()->getStore()->getId();
        
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $weeklydeal = array();
        $consecutive7days = array();
        $i = 0;
        $m = date('m', Mage::getModel('core/date')->timestamp(time()));
        $d = date('d', Mage::getModel('core/date')->timestamp(time()));
        $Y = date('Y', Mage::getModel('core/date')->timestamp(time()));
        while ($i < 7) {

            array_push($consecutive7days, date('Y-m-d', mktime(0, 0, 0, $m, $d + $i, $Y)));
            $i++;
        }
        $weeklyCollection = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view',array(array('like'=>'%'. Mage::app()->getStore()->getId() .'%'),array('like'=>'0')))
                ->addProductStatusFilter($store_id)
                ->getConfigSortBy();

        $weeklyCollection->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );

        $weeklyCollection->getSelect()->where("stock.is_in_stock = " . Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);
        
        
        // Remove deal end_date_time < current_date_time - begin
        $current_time = Mage::getModel('core/date')->timestamp(time());
        if (count($weeklyCollection) > 0) {
            foreach($weeklyCollection->getItems() as $key => $_deal){
                $stat_time = strtotime($_deal->getData('start_date_time'));
                $end_time = strtotime($_deal->getData('end_date_time'));
                if($end_time < $current_time){
                    $weeklyCollection->removeItemByKey($key);
                }
            }
        }
        // Remove deal end_date_time < current_date_time - end
        
        if (count($weeklyCollection) > 0) {
            foreach ($weeklyCollection as $weekly) {
                $Ystart = date('Y', strtotime($weekly->getStartDateTime()));
                $mstart = date('m', strtotime($weekly->getStartDateTime()));
                $dstart = date('d', strtotime($weekly->getStartDateTime()));
                $daysdeal = (strtotime($weekly->getEndDateTime()) - strtotime($weekly->getStartDateTime())) / 86400;

                $formatdealend = date('Y-m-d', strtotime($weekly->getEndDateTime()));
                $seekday = '';
                // select in 7 day
                for ($j = 0; $j < $daysdeal + 1; $j++) {

                    $seekday = date('Y-m-d', mktime(0, 0, 0, $mstart, $dstart + $j, $Ystart));
                    if (in_array($seekday, $consecutive7days) && !in_array($seekday, $weeklydeal)) {
                        array_push($weeklydeal, $seekday);
                    }
                    if ($seekday == $formatdealend) {
                        break;
                    }
                }
            }
        }
        
        return $weeklydeal;
    }
    
    public function renderDealFollowDayHtml($days){
        
        $store_id = Mage::app()->getStore()->getId();
        
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $dayselect = $days;
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
        return  $result;
    }
}