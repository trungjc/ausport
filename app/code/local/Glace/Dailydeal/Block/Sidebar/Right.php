<?php

class Glace_Dailydeal_Block_Sidebar_Right extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        $dailydeal = Mage::getStoreConfig('dailydeal/general/sidebardeal');
        $activedeal = Mage::getStoreConfig('dailydeal/general/sidebaractive');
        $calendar = Mage::getStoreConfig('dailydeal/general/calendar');

        if ($dailydeal == 2 || $activedeal == 2 || $calendar == 2)
            $this->setTemplate('glace_dailydeal/sidebar/right_staticsidebar.phtml');
    }

    //getcollection 1 mang cac deal kich hoat, sap xep theo tu tu kich hoat trc tu tren xuong
    //de dang chon dc deal dc uu tien
    public function getDeals($showtotal) {
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', '1')
                ->addFieldToFilter('featured', '1')
                ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                ->addFieldToFilter('sold_qty', array('lt' => 'deal_qty'))
                ->addAttributeToSort('dailydeal_id', 'ASC')
                ->addAttributeToSort('start_date_time', 'ASC');
        $deals->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );
        //$deals->getSelect()->where("stock.qty > 0");        
        $deals->getSelect()->where("stock.is_in_stock = 1");
        if ($deals->count() > 0) {
            //echo 'death';//die;						
            return $deals;
        } else {
            $deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
                    ->addFieldToFilter('status', '1')
                    ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                    ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                    ->addAttributeToSort('dailydeal_id', 'ASC')
                    ->addAttributeToSort('start_date_time', 'ASC');
            $deals->getSelect()->joinLeft(
                    array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
            );
            //$deals->getSelect()->where("stock.qty > 0");        
            $deals->getSelect()->where("stock.is_in_stock = 1");
            return $deals;
        }
    }

    public function getActivedeals() {
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', '1')
                ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                ->addAttributeToSort('start_date_time', 'ASC');
        $deals->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );
        //$deals->getSelect()->where("stock.qty > 0");        
        $deals->getSelect()->where("stock.is_in_stock = 1");
        $deals->getSelect()->where("deal_qty > sold_qty");
        $deals->load();
        return $deals;
    }

    public function getWeeklydeal() {
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
        //	Zend_Debug::dump($consecutive7days); echo '<br/>';
        $weeklyCollection = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', 1);
        $weeklyCollection->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );
        //$weeklyCollection->getSelect()->where("stock.qty > 0");        
        $weeklyCollection->getSelect()->where("stock.is_in_stock = 1");
        //								->addFieldToFilter('start_date_time',array('to' => $currenttime));
        if (count($weeklyCollection) > 0) {
            foreach ($weeklyCollection as $weekly) { //echo date('Y-m-d',strtotime($weekly->getStartDateTime()));
                $Ystart = date('Y', strtotime($weekly->getStartDateTime()));
                $mstart = date('m', strtotime($weekly->getStartDateTime()));
                $dstart = date('d', strtotime($weekly->getStartDateTime()));
                $daysdeal = (strtotime($weekly->getEndDateTime()) - strtotime($weekly->getStartDateTime())) / 86400;
                $formatdealend = date('Y-m-d', strtotime($weekly->getEndDateTime())); //echo $formatdealend.'<br/>';
                //$j = 0;
                $seekday = '';
                //Neu trong vong 7 ngay ma ko co ngay nao trung thi tat nhien se quit vong lap
                for ($j = 0; $j < $daysdeal + 1; $j++) {
                    //Chay tu ngay bat dau deal cho den ngay ket thuc
                    $seekday = date('Y-m-d', mktime(0, 0, 0, $mstart, $dstart + $j, $Ystart));
                    if (in_array($seekday, $consecutive7days) && !in_array($seekday, $weeklydeal)) {
                        array_push($weeklydeal, $seekday);
                    }
                    //$j++;
                    //var_dump($seekday);
                    if ($seekday == $formatdealend) {
                        break;
                    }
                }//die;
            }
        }
        return $weeklydeal;
    }

}