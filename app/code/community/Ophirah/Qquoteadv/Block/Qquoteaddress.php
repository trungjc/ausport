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

class Ophirah_Qquoteadv_Block_Qquoteaddress extends Mage_Core_Block_Template
{

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Get customer session data
     * @return session data
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Returns the customer email
     *
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getCustomerSession()->getCustomer()->getEmail();
    }

    /**
     * Check if customer is loggedin
     *
     * @return mixed
     */
    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * Function to extract data from a post form
     *
     * @param $fieldname
     * @param $type
     * @return null|string
     */
    public function getValue($fieldname, $type)
    {
        if ($value = $this->_getRegisteredValue($type)) {

            // When quote data is stored
            // address data is an array
            // Create object from array
            if(is_array($value)){
                $newValue = new Varien_Object();
                $newValue->setData($value);
                $value= $newValue;
            }

            if ($fieldname == "street1") {
                $street = $value->getData('street');
                if (is_array($street)) {
                    $street = explode("\n", $street);
                    return $street[0];
                } else {
                    return "";
                }
            }

            if ($fieldname == "street2") {
                $street = $value->getData('street');

                if (is_array($street)) {
                    $street = explode("\n", $street);
                    return $street[1];
                } else {
                    return "";
                }
            }

            if ($fieldname == "email") {
                return $this->getCustomerSession()->getCustomer()->getEmail();
            }

            if ($fieldname == "country") {
                $countryCode = $value->getData("country_id");
                return $countryCode;
            }
            return $value->getData($fieldname);
        }

        return null;
    }

    /**
     * Returns an address based on a type, if it is available
     *
     * @param string $type
     * @return null
     */
    protected function _getRegisteredValue($type = 'billing')
    {

        // When Quote Shipping Estimate is requested
        // use data from session
        if ($quoteAddresses = $this->getCustomerSession()->getData('quoteAddresses')) {

            if ($type == 'billing' && isset($quoteAddresses['billingAddress'])) {
                return $quoteAddresses['billingAddress'];
            }

            if ($type == 'shipping' && isset($quoteAddresses['shippingAddress'])) {
                return $quoteAddresses['shippingAddress'];
            }
        }
        // Default data
        if ($type == 'billing') {
            return $this->getCustomerSession()->getCustomer()->getPrimaryBillingAddress();
        }

        if ($type == 'shipping') {
            return $this->getCustomerSession()->getCustomer()->getPrimaryShippingAddress();
        }

        return null;
    }

    /**
     * Function that gets the login url for a customer and gives it a referer path if the settings allow that
     *
     * @return mixed
     */
    public function getLoginUrl()
    {

        if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
            return $this->getUrl('customer/account/login/', array('referer' => $this->getUrlEncoded('*/*/*', array('_current' => true))));
        }

        return $this->getUrl('customer/account/login/');
    }

    /**
     * Retrieve storeConfigData from
     * config_data table
     *
     * @param $fieldPrefix
     * @param $field
     * @param string $storeId
     * @return bool|mixed
     */
    public function getStoreConfigSetting($fieldPrefix, $field, $storeId = "1")
    {
        $return = false;

        if ($fieldPrefix != null && $field != null) {
            $storeSetting = Mage::getStoreConfig($fieldPrefix . $field, $storeId);
            if ($storeSetting > 0) {
                $return = $storeSetting;
            }
        }

        return $return;
    }

    /**
     * Check is field is required in
     * the store config settings
     *
     * @param $fieldPrefix
     * @param $field
     * @param string $storeId
     * @return bool|Varien_Object
     */
    public function isRequired($fieldPrefix, $field, $storeId = "1")
    {
        $storeSetting = $this->getStoreConfigSetting($fieldPrefix, $field, $storeId);

        if (!$storeSetting) {
            return false;
        }

        $return = new Varien_Object;
        $required = '<span class="required">*</span>';
        $class = 'required-entry';
        if ((int)$storeSetting == 2) {
            $return->setData('required', $required);
            $return->setData('class', $class);
            return $return;
        }

        return $return;
    }

    /**
     * Returns complete address in table rows.
     * @param $addressType
     * @return string
     */
    public function getMultipleLineAddress($addressType){
        $numberOfLines = Mage::getStoreConfig('customer/address/street_lines');
        $addressRows = '';

        for($line = 0; $line < $numberOfLines; $line++){
            if($addressType == 'shipping'){
                $addressRows .= $this->_getShippingAddressLine($line);
            }else{
                $addressRows .= $this->_getBillingAddressLine($line);
            }
        }
        return $addressRows;
    }

    /**
     * Returns single billing address line
     * @param int $lineNumber
     * @return string
     */
    protected function _getBillingAddressLine($lineNumber = 0){
        $addressLineValue = Mage::helper('qquoteadv/address')->splitMultipleLineAddress($this->getValue('street', 'billing'));
        if($lineNumber == 0){
            $headerText = Mage::helper('sales')->__('Address').'<span class="required"></span><br/>';
            $required = 'required-entry';
        }else{
            $headerText = "";
            $required = '';
        }

        //check for empty address lines
        if(!isset($addressLineValue[$lineNumber])){
            return '';
        }

        $html = '
                <tr style="margin-bottom: 1px;">
                    <td class="left">'.$headerText.'
                        <input onfocus="Element.setStyle(this, {color:\'#2F2F2F\'});" type=\'text\'
                               value="'.$addressLineValue[$lineNumber].'"
                               name=\'customer[address'.$lineNumber.']\'
                               id=\'customer:address'.$lineNumber.'\' class="'.$required.' input-text addr w224"/>
                    </td>
                    <td class="p5"></td>
                </tr>';
        return $html;
    }

    /**
     * Returns single shipping address line
     * @param int $lineNumber
     * @return string
     */
    protected function _getShippingAddressLine($lineNumber = 0){
        $addressLineValue = Mage::helper('qquoteadv/address')->splitMultipleLineAddress($this->getValue('street', 'shipping'));
        if($lineNumber == 0){
            $headerText = Mage::helper('sales')->__('Address').'<span class="required"></span><br/>';
            $required = 'required-entry';
        }else{
            $headerText = "";
            $required = '';
        }

        //check for empty address lines
        if(!isset($addressLineValue[$lineNumber])){
            return '';
        }

        $html = '
                <tr style="margin-bottom: 1px;">
                    <td class="left">'.$headerText.'
                        <input onfocus="Element.setStyle(this, {color:\'#2F2F2F\'});" type=\'text\'
                               value="'.$addressLineValue[$lineNumber].'"
                               name=\'customer[shipping_address'.$lineNumber.']\'
                               id=\'customer:shipping_address'.$lineNumber.'\' class="'.$required.' input-text addr w224"/>
                    </td>
                    <td class="p5"></td>
                </tr>';
        return $html;
    }
}
