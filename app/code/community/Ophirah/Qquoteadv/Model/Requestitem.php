<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

class Ophirah_Qquoteadv_Model_Requestitem extends Mage_Sales_Model_Quote_Address_Item
{
    protected $_quote = null;
    protected $_weight = null;
    protected $_children = null;
    protected $_hasChildren = null;
    protected $_taxableAmount = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/requestitem');

    }

    /**
     * Add item to request for the particular quote
     * @param array $params array of field(s) to be inserted
     * @return mixed
     */
    public function addItem($params, $productData = null)
    {
        if (!$this->_isDublicatedData($params, $productData)) {
            $this->setData($params);
            $this->save();
        }
        return $this;
    }

    /**
     * Add items to request for the particular quote
     * @param array $params array of field(s) to be inserted
     * @return mixed
     */
    public function addItems($params)
    {

        foreach ($params as $key => $values)
//            if(!$this->_isDublicatedData($values)){
            $this->addItem($values);
//           }
        return $this;
    }

    /**
     * Checking item / qty for blocking dublication request
     * @param array $params array of field(s) should to be inserted
     * @return mixed
     */
    protected function _isDublicatedData($params, $productData = null)
    {
        $quoteProduct = Mage::getSingleton('qquoteadv/qqadvproduct')->load($params['quoteadv_product_id']);
        $identicalAttribute =false;
        if(isset($productData) && isset($quoteProduct)){
            if(isset($productData['attribute'])){
                $quoteProductAttribute = $quoteProduct->getData('attribute');
                if($quoteProductAttribute == $productData['attribute']){
                    $identicalAttribute = true;
                }
            }
        }

        $quoteId = $params['quote_id'];
        $productId = $params['product_id'];
        $qtyRequest = $params['request_qty'];

        $_quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($quoteId);

        $collection = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($_quote)
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('request_qty', $qtyRequest)
            ->addFieldToFilter('quoteadv_product_id',$params['quoteadv_product_id'])//->load(true)
        ;

        return $collection->count() > 0 && $identicalAttribute;
    }

    public function getProduct()
    {
        $product = Mage::getSingleton('catalog/product')->load($this->getProductId());

        $qqadvproduct = Mage::getModel('qquoteadv/qqadvproduct')->load($this->getQuoteadvProductId());

        $product->setStoreId($qqadvproduct->getStoreId() ? $qqadvproduct->getStoreId() : 1);
        //$productOptions = unserialize($qqadvproduct->getAttribute());
        $buyRequest = new Varien_Object();
        $product->getTypeInstance($product)->processConfiguration($buyRequest, $product);
        return $product;

    }

    public function setQuote($quote)
    {
        $this->_quote = $quote;
    }

    public function getQuote()
    {

        if ($this->_quote == null) {
            $quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load((int)$this->getQuoteId());
            $this->_quote = $quote;
        }
        return $this->_quote;
    }

    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    public function getAddress()
    {
        return $this->getQuote()->getAddress();
    }


    /**
     * Calculate item row total price
     *
     * @return Ophirah_Qquoteadv_Model_Requestitem
     */
    public function calcRowTotal()
    {
        $qty = $this->getRequestQty();
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        $total = $this->getStore()->roundPrice($this->getOwnerCurPrice()) * $qty;
        $baseTotal = $this->getOwnerBasePrice() * $qty;

        $this->setRowTotal($this->getStore()->roundPrice($total));
        $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
        return $this;
    }

    public function getQuoteItemId()
    {
        return $this->getId();
    }

    public function getOriginalPrice()
    {
        return $this->getOwnerCurPrice();
    }

    public function getBaseOriginalPrice()
    {
        return $this->getOwnerBasePrice();
    }

    public function getTotalQty()
    {
        return $this->getRequestQty();
    }

    public function getQty()
    {
        return $this->getRequestQty();
    }

    public function getCalculationPrice()
    {
        return $this->getOwnerCurPrice();
    }

    public function getBaseCalculationPrice()
    {
        return $this->getOwnerBasePrice();
    }

    public function getCalculationPriceOriginal()
    {
        return $this->getOwnerCurPrice();
    }

    public function getBaseCalculationPriceOriginal()
    {
        return $this->getOwnerBasePrice();
    }

    public function getRowTotal()
    {
        $qty = $this->getRequestQty();
        $total = $this->getStore()->roundPrice($this->getOwnerCurPrice()) * $qty;
        return $total;
    }

    public function getBaseRowTotal()
    {
        $qty = $this->getRequestQty();
        $baseTotal = $this->getOwnerBasePrice() * $qty;
        return $baseTotal;
    }

    public function getWeight()
    {

        if ($this->_weight == null) {
            $this->_weight = $this->getProduct()->getWeight();
        }
        return $this->_weight;
    }

    public function getConfChildProduct()
    {
        return Mage::getModel('qquoteadv/qqadvproduct')->getConfChildProduct($this->getData('quoteadv_product_id'));
    }
}
