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

class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Set product template to display product information in admin tab
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('qquoteadv/product.phtml');
        $this->iniBlocks();
    }

    /**
     * Initialize child-blocks
     */
    public function iniBlocks(){
        $this->setExtraFieldsBlock();
        $this->setMultiUploadBlock();
        $this->setCrmAddonAttachmentBlock();
        $this->setCrmAddonNewAttachmentBlock();
    }

    /**
     * Get Product information from qquote_product table
     * @return object
     */
    public function getProductData()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $product = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
        return $product;
    }

    /**
     * Get product Information
     * @param integer $productId
     * @return object
     */
    public function getProductInfo($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Get attribute options array
     * @param object $product
     * @param string $attribute
     * @return array
     */
    public function getOption($product, $attribute)
    {
        $superAttribute = array();
        if ($product->isConfigurable()) {
            $superAttribute = Mage::getModel('qquoteadv/configurable')->getSelectedAttributesInfoText($product, $attribute);
        }

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute));
        }
        return $superAttribute;
    }


    /**
     * Get Product information from qquote_request_item table
     * @return object
     */
    public function getRequestedProductData($id, $quote)
    {
//        $quoteId = $this->getRequest()->getParam('id');  
        $product = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
            ->addFieldToFilter('quoteadv_product_id', $id);

        $product->getSelect()->order('request_qty asc');
        return $product;
    }

    /**
     * Return quote by quote id
     *
     * @param int $quoteId
     * @return collection
     */
    public function getQuoteInfo()
    {
        $quoteId = $this->getRequest()->getParam('id');
        return Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
    }

    /**
     * Get the shipping price
     * (Has a fallback function for when shipping price is not set)
     *
     * @return null
     */
    public function getQuoteShipPrice()
    {
        $shippingPrice = $this->getQuoteInfo()->getShippingPrice();
        //0.00 is also price
        return ($shippingPrice > -1) ? Mage::app()->getStore()->roundPrice($shippingPrice) : null;
    }

    /**
     * Shipping price check
     *
     * @return bool
     */
    public function isAvaliableShipPrice()
    {
        //0.00 is also price
        return ($this->getQuoteShipPrice() > -1) ? true : false;
    }

    /**
     * Get Quote information from qquote_customer table
     * @return object
     */
    public function getQuoteData($collectTotals = true)
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);

        // Set correct store for the quote
        if(count($quote->getCurrency()) > 1){
            $quote->getStore()->setCurrentCurrency(Mage::getModel('directory/currency')->load($quote->getCurrency()));
        }
        // Collect totals
        if ($collectTotals) {
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
        }
        return $quote;
    }

    /**
     * Function that returns the shipping address in a given format
     * (Default format is html)
     *
     * @param $customer_id
     * @param string $format
     * @return null
     */
    public function getShippingAddress($customer_id, $format = "html")
    {
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $address = $customer->getDefaultShippingAddress();

        if (!$address) {
            foreach ($customer->getAddresses() as $address) {
                if ($address) {
                    break;
                }
            }
        }

        if (!$address) {
            return null;
        }

        return $address->format($format);
    }

    /**
     * Get the customer group name based on the customer id or customer group id
     *
     * @param $customer_id
     * @param null $customerGroupId
     * @return null
     */
    public function getCustomerGroupName($customer_id, $customerGroupId = null)
    {
        if($customerGroupId == null){
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            if ($groupId = $customer->getGroupId()) {
                $customerGroupId = $groupId;
            }
        }

        if($customerGroupId != null){
            return Mage::getModel('customer/group')
                ->load($customerGroupId)
                ->getCustomerGroupCode();
        }

        return null;
    }

    /**
     * Get the url in the backend to edit the customer based on customer id
     *
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerViewUrl($customer_id)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $customer_id));
    }

    /**
     * Get customer name based on customer id
     *
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerName($customer_id)
    {
        return Mage::getModel('customer/customer')->load($customer_id)->getName();
    }

    /**
     * Get info of the store based on the store id
     * (Has a fallback when the store id is not set)
     *
     * @param $storeId
     * @return string
     */
    public function getStoreViewInfo($storeId)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }

        $store = Mage::app()->getStore($storeId);
        $params[] = $store->getWebsite()->getName();
        $params[] = $store->getGroup()->getName();
        $params[] = $store->getName();

        return implode('<br />', $params);
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        $params = array(
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
        );
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Get the admin name based on the admin id
     *
     * @param $id
     * @return mixed
     */
    public function getAdminName($id)
    {
        return Mage::helper('qquoteadv')->getAdminName($id);
    }

    /**
     * Create update button
     *
     * @param
     *
     */
    public function getUpdateTotalButton($status = NULL, $onclick = '')
    {

        if ($status == 'disabled') {
            $class = 'disabled';
        } else {
            $class = '';
            $onclick = "$('loading-mask').show();save();";
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel(Mage::helper('checkout')->__('Update Total'));
        $button->setClass($class);

        return $button;
    }

    /**
     * ** DEPRECATED **
     * Create Coupon button
     *
     * @param
     */
    public function getCouponButton()
    {

        $class = '';
        $onclick = "$('loading-mask').show();save();";

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel(Mage::helper('rule')->__('Apply'));
        $button->setClass($class);

        return $button;
    }

    /**
     * ** DEPRECATED **
     * Create FixedPrice button
     *
     * @param
     */
    public function getFixedPriceButton()
    {

        $class = '';
        $onclick = "$('loading-mask').show();save();";

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel($this->__('Recalculate'));
        $button->setClass($class);

        return $button;
    }

    /**
     * Create a save button
     *
     * @param Varien_Object $vars
     * @return boolean
     */
    public function getSaveButton($vars = null, $onclick = false)
    {
        if (!$vars instanceof Varien_Object) {
            return false;
        }

        if(!$onclick){
            $onclick = (!$vars->getData('onclick')) ? "$('loading-mask').show();save();" : $vars->getData('onclick');
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel($vars->getData('label'));
        $button->setClass($vars->getData('class'));

        return $button;
    }

    /**
     * Setup the extra fields block as child for this block.
     */
    public function setExtraFieldsBlock(){
        $childBlock =  Mage::getSingleton('core/layout')
            ->createBlock('qquoteadv/adminhtml_qquoteadv_quotedetails_extrafields')
            ->setTemplate('qquoteadv/details/extra_fields.phtml');
        $this->setChild('quoteExtraFields', $childBlock);
    }

    /**
     * Setup the extra fields block as child for this block.
     */
    public function setMultiUploadBlock(){
        $childBlock =  Mage::getSingleton('core/layout')
            ->createBlock('qquoteadv/adminhtml_qquoteadv_quotedetails_multiupload')
            ->setTemplate('qquoteadv/details/multi_upload.phtml');
        $this->setChild('quoteMultiUpload', $childBlock);
    }

    /**
     * Setup the CRMaddon block as child for this block.
     */
    public function setCrmAddonAttachmentBlock(){
        $childBlock =  Mage::getSingleton('core/layout')
            ->createBlock('crmaddon/adminhtml_attachment')
            ->setTemplate('qquoteadv/crmaddon/attachment.phtml');
        $this->setChild('crmaddon.attachment', $childBlock);
    }

    /**
     * Setup the CRMaddon new attachment block as child for this block.
     */
    public function setCrmAddonNewAttachmentBlock(){
        $childBlock =  Mage::getSingleton('core/layout')
            ->createBlock('crmaddon/adminhtml_attachment')
            ->setTemplate('qquoteadv/crmaddon/attachment_new.phtml');
        $this->setChild('crmaddon.attachment.new', $childBlock);
    }

}
