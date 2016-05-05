<?php

class Glace_Dailydeal_Block_Showtabs_Comming extends Mage_Catalog_Block_Product_List
{

    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $store_id = Mage::app()->getStore()->getId();
            $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $collection = Mage::getModel('dailydeal/dailydeal')->getCollection()
                    ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                    ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')))
                    ->addFieldToFilter('start_date_time', array('from' => $currenttime))
                    ->addProductStatusFilter($store_id);

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
    
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        
        $toolbar->setAvailableOrders(array());  // clear
        $toolbar->addOrderToAvailableOrders('start_date_time', Mage::helper('dailydeal')->__('Time'));
        $toolbar->addOrderToAvailableOrders('cur_product', Mage::helper('catalog')->__('Name'));
        $toolbar->addOrderToAvailableOrders('dailydeal_price', Mage::helper('catalog')->__('Price'));
        
        $toolbar->setDefaultDirection('asc');

        $collection = $this->_getProductCollection();
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);

    }

    public function getCommingdeals()
    {
        return $this->_getProductCollection();
    }

    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $return = '';

        $deal = Glace_Dailydeal_Model_Dailydeal::getInstance();
        if ($deal->checkDealPrice($product) == false) {
            $temp = Mage::getModel("catalog/product")->getCollection()
                    ->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes())
                    ->addAttributeToFilter("entity_id", $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem();
            $product = $temp;
            $return = parent::getPriceHtml($product, true);
        } else {
            //$this->setTemplate('catalog/product/price.phtml');
            //$this->setProduct($product);
            //$return = $this->toHtml();
            $return = parent::getPriceHtml($product, true);
        }

        return $return;
    }

}