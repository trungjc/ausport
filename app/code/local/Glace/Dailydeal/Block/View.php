<?php

/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file PRICE COUNTDOWN-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.x and 1.5.x COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.x and 1.5.x COMMUNITY edition.
 * =================================================================
 */
class Glace_Dailydeal_Block_View extends Mage_Core_Block_Template
{

    protected function _prepareLayout()
    {
//$block = $this->getLayout()->getBlock('product.info.addtocart');
//if ($block){ echo 'ikazuchi';die;
//	$block->setTemplate('glace_dailydeal/timer/view.phtml');
//}
        return parent::_prepareLayout();
    }

    public function getTodayDeal($_product)
    {
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $deals = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', '1')
                ->addFieldToFilter('featured', '1')
                ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
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

}

?>