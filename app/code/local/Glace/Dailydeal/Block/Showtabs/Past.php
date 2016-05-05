<?php

class Glace_Dailydeal_Block_Showtabs_Past extends Mage_Catalog_Block_Product_List
{
    
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {

            $store_id = Mage::app()->getStore()->getId();
            $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $collection = Mage::getModel('dailydeal/dailydeal')->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')))
                ->addProductStatusFilter($store_id);
                /*
                 * Magento's version is smaller than 1.7 not run ( error )
                ->addFieldToFilter(array('end_date_time', 'expire'),array(
                    array('to' => $currenttime),
                    array('expire' => Glace_Dailydeal_Model_Status::STATUS_EXPIRE_TRUE)
                ));
                */
                $collection->getSelect()->where("
                    (end_date_time <= '{$currenttime}') OR (((expire = '". Glace_Dailydeal_Model_Status::STATUS_EXPIRE_TRUE . "')))
                ");


            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        
        $toolbar->setAvailableOrders(array());  // clear
        $toolbar->addOrderToAvailableOrders('end_date_time', Mage::helper('dailydeal')->__('Time'));
        $toolbar->addOrderToAvailableOrders('cur_product', Mage::helper('catalog')->__('Name'));
        $toolbar->addOrderToAvailableOrders('dailydeal_price', Mage::helper('catalog')->__('Price'));
        
        $toolbar->setDefaultDirection('desc');

        $collection = $this->_getProductCollection();
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);

    }

    public function getPastdeals()
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