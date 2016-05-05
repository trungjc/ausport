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

class Ophirah_Qquoteadv_Model_Address extends Mage_Sales_Model_Quote_Address
{
    CONST DEFAULT_DEST_STREET = -1;
    protected $_quote = null;
    protected $_rates = null;

    protected $_itemsQty = null;

    public $_shippingRates = null;
    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'ophirah_qquoteadv_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quoteadv_address';

    /**
     * Override resource as we are defining the field ourselves
     */
    protected function _construct()
    {
        $this->_init('qquoteadv/address');
    }

    /**
     * Init mapping array of short fields to its full names
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _initOldFieldsMap()
    {
        return $this;
    }

    /**
     * Initialize quote identifier before save
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        return $this;
    }

    /**
     * Declare adress quote model object
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Retrieve address items collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (is_null($this->_items)) {
            $items = $this->getAllItems();
            foreach ($items as $item) {
                $item->setAddress($this);
                $item->setQuote($this->getQuote());
            }
        }
        return $items;
    }

    /**
     * Get all available address items
     *
     * @return array
     */
    public function getAllItems()
    {
        return $this->getQuote()->getAllRequestItems();
    }

    /**
     * Get combined weight of the
     * quote products
     *
     * @return \Ophirah_Qquoteadv_Model_Address
     */
    public function getWeight()
    {
        if ($this->getQuote() instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            return $this->getQuote()->getWeight();
        }

        return $this;
    }

    /**
     * Retrieve item quantity by id
     *
     * @param int $itemId
     * @return float|int
     */
    public function getItemQty($itemId = 0)
    {
        if ($this->_itemsQty == null) {
            $this->_itemsQty = 0;
            $items = $this->getAllItems();
            foreach ($items as $item) {
                // skip non visible items
                if ($item->getParentItem()) {
                    continue;
                }
                // If items get shipped seperatly
                if ($item->isShipSeparately() && $item->getData('qty_options')) {
                    foreach ($item->getData('qty_options') as $optionItem) {
                        $this->_itemsQty += $optionItem->getProduct()->getData('qty');
                    }
                } else {
                    $this->_itemsQty += $item->getData('qty');
                }
            }
        }

        return $this->_itemsQty;
    }


    /**
     * Add item to address
     *
     * @param   Ophirah_Qquoteadv_Model_Requestitemt $item
     * @param   int $qty
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function addItem(Mage_Sales_Model_Quote_Item_Abstract $item, $qty = null)
    {

        return $this;
    }

    /**
     * Getter for address id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getData('address_id');
    }

    /**
     * Overwrite for getCollectShippingRates to return always true
     *
     * @return bool
     */
    public function getCollectShippingRates()
    {
        return true;
    }

    /**
     * Clear $_rates to
     * rebuild shippingrates collection
     */
    public function clearRates()
    {
        $this->_rates = null;
    }

    /**
     * Retrieve collection of quote shipping rates
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getShippingRatesCollection()
    {
        if ($this->_rates == null) {
            $this->_rates = array();
            if ($this->getQuote()->getIsCustomShipping()) {

                $price = $this->getQuote()->getShippingBasePrice();

                if ($this->getQuote()->getShippingType() == "I") {
                    $price = ($price * $this->getQuote()->getItemsQty());
                }

                $rate = Mage::getModel('qquoteadv/shippingrate');
                $rate->setData('carrier', 'qquoteshiprate');
                $rate->setData('carrier_title', 'Flat Rate');
                $rate->setData('price', $price);
                $rate->setData('cost', $price);
                $rate->setData('method', 'qquoteshiprate');
                $rate->setData('method_title', 'Fixed');
                $rate->setData('method_description', 'Fixed');
                $quoteRate = Mage::getModel('sales/quote_address_rate')->importShippingRate($rate);
                $this->_rates = array($quoteRate);
            } else {
                // Note: we cant use $this->collectShippingRates(); here,
                // that would cause an infinite loop
                $this->_rates = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                    ->addFieldToFilter('address_id', array('eq' => $this->getData('address_id')))
                    ->addFieldToFilter('active', array('eq' => 1));

                if ($this->hasNominalItems(false)) {
                    $this->_rates->setFixedOnlyFilter(true);
                }
                if ($this->getId()) {
                    foreach ($this->_rates as $rate) {
                        $rate->setAddress($this);

                    }
                }
            }
        }

        return $this->_rates;
    }

    /**
     * Function that collects/calculates all shipping rates for this address
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        if (!$this->getCollectShippingRates()) {
            return $this;
        }

        $this->removeAllShippingRates();

        if (!$this->getCountryId()) {
            return $this;
        }
        $found = $this->requestShippingRates();
        if (!$found) {
            $this->setShippingAmount(0)
                ->setBaseShippingAmount(0)
                ->setShippingMethod('')
                ->setShippingDescription('');
        }
        return $this;
    }

    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($item ? array($item) : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        /**
         * need to call getStreet with -1
         * to get data in string instead of array
         */
        $request->setDestStreet($this->getStreet(self::DEFAULT_DEST_STREET));
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageValueWithDiscount = $item
            ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
            : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());

        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item
            ? $item->getBaseRowTotal()
            : $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());

        /**
         * Store and website identifiers need specify from quote
         */
        $request->setStoreId($this->getQuote()->getStore()->getId());
        $request->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());

        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->getQuote()->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->getQuote()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());

        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax() + $this->getBaseExtraTaxAmount());

        $result = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();

        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();

            // Reset existing rates
            if ($shippingRates) {
                // Load QuoteRate Collection
                $collection = Mage::getModel('qquoteadv/quoteshippingrate')->resetQuoteRates($this->getData('address_id'));
            }

            foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')
                    ->importShippingRate($shippingRate);

                if (!$item) {
                    $this->addQuoteShippingRate($rate, $collection);
                }

                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        /**
                         * possible bug: this should be setBaseShippingAmount(),
                         * see Mage_Sales_Model_Quote_Address_Total_Shipping::collect()
                         * where this value is set again from the current specified rate price
                         * (looks like a workaround for this bug)
                         */

                        $this->setBaseShippingAmount($rate->getPrice());
                    }

                    $found = true;
                }

            }
        }

        return $found;
    }

    /**
     * Add / Update Quote Shipping rate table
     *
     * @param Mage_Sales_Model_Quote_Address_Rate $rate
     * @return boolean
     */
    public function addQuoteShippingRate(Mage_Sales_Model_Quote_Address_Rate $rate, $collection = null)
    {

        if ($collection == null) {
            $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                ->addFieldToFilter('address_id', array('eq' => $this->getData('address_id')))
                ->load();
        }

        // update existing shipping data
        if (!$collection === false) {
            foreach ($collection as $updateRate) {
                if ($updateRate->getData('code') == $rate->getData('code')) {
                    $updateRate->addData($rate->getData());
                    $updateRate->setData('updated_at', NOW());
                    $updateRate->setData('active', 1);
                    $updateRate->save();
                    return;
                }
            }
        }

        // Add new shippingdata
        $newRate = Mage::getModel('qquoteadv/quoteshippingrate');
        $newRate->addData($rate->getData());
        $newRate->setData('address_id', $this->getData('address_id'));
        $newRate->setData('created_at', NOW());
        $newRate->setData('updated_at', NOW());
        $newRate->save();
        return;
    }

    /**
     * Getter for the street name
     *
     * @param int $line
     * @return mixed
     */
    public function getStreet($line = 0)
    {
        return $this->getQuote()->getStreet($line);
    }

    /**
     * Getter for the region id
     *
     * @return mixed
     */
    public function getRegionId()
    {
        return $this->getQuote()->getRegionId();
    }

    /**
     * Getter for the country id
     *
     * DEPRECATED
     * From v4.2.1. The country Id is
     * within the address()
     * No need to call the ShippingCounrtyId()
     *
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->getQuote()->getCountryId();
    }

    /**
     * Getter for the city name
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->getQuote()->getCity();

    }

    /**
     * Getter for the postcode
     *
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->getQuote()->getPostcode();
    }


    /**
     * Retrieve all address shipping rates
     *
     * @return array
     */
    public function getAllShippingRates()
    {
        $rates = array();
        foreach ($this->getShippingRatesCollection() as $rate) {
            $rates[] = $rate;
        }
        return $rates;
    }

    /**
     * Get totals collector model
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Collector
     */
    public function getTotalCollector()
    {
        if ($this->_totalCollector === null) {
            $this->_totalCollector = Mage::getSingleton(
                'sales/quote_address_total_collector',
                array('store' => $this->getQuote()->getStore())
            );
        }
        return $this->_totalCollector;
    }

    /**
     * Retrieve total models
     *
     * @deprecated
     * @return array
     */
    public function getTotalModels()
    {
        return $this->getTotalCollector()->getRetrievers();
    }

    /**
     * Collect address totals
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function collectTotals()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));

        $storeId = $this->getQuote()->getStoreId();
        $store = Mage::app()->getStore($storeId);

        $collectors = $this->getTotalCollector()->getCollectors();
        $collectors['c2qtotal']->collect($this);

        //Check if freeshipping is enabled before calculating it
        $freeShipping = Mage::getStoreConfig('carriers/freeshipping/active', $store);
        if(!empty($freeShipping) && $freeShipping == 1){
            $collectors['freeshipping']->collect($this);
        }

        //fix for based on shipping orgin - part 1 of 2
        $orgCountry = Mage::getStoreConfig("shipping/origin/country_id", $store);
        $orgRegion = Mage::getStoreConfig("shipping/origin/region_id", $store);
        $orgPostcode = Mage::getStoreConfig("shipping/origin/postcode", $store);

        $tempCountry = Mage::getStoreConfig("tax/defaults/country", $store);
        $tempRegion = Mage::getStoreConfig("tax/defaults/region", $store);
        $tempPostcode = Mage::getStoreConfig("tax/defaults/postcode", $store);

        $store->setConfig("shipping/origin/country_id", $tempCountry);
        $store->setConfig("shipping/origin/region_id", $tempRegion);
        $store->setConfig("shipping/origin/postcode", $tempPostcode);
        // end fix - part 1 of 2

        $collectors['tax_subtotal']->collect($this);

        //Check if weee is enabled before calculating it
        $weee = Mage::getStoreConfig('tax/weee/enable', $store);
        if(!empty($weee) && $weee == 1){
            $collectors['weee']->collect($this);
        }

        //fix for based on shipping orgin - part 2 of 4
        $store->setConfig("shipping/origin/country_id", $orgCountry);
        $store->setConfig("shipping/origin/region_id", $orgRegion);
        $store->setConfig("shipping/origin/postcode", $orgPostcode);
        // end fix - part 2 of 4

        $collectors['shipping']->collect($this);

        //multi vat (posible)
        $collectors['tax_shipping']->collect($this);

        //fix for based on shipping orgin - part 3 of 4
        $store->setConfig("shipping/origin/country_id", $tempCountry);
        $store->setConfig("shipping/origin/region_id", $tempRegion);
        $store->setConfig("shipping/origin/postcode", $tempPostcode);
        // end fix - part 3 of 4

        $collectors['discount']->collect($this);
        $collectors['tax']->collect($this);

        //surcharge, after tax calculation and using calculate or collect based on version number
        if(Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')){
            $version = Mage::getConfig()->getNode()->modules->Fooman_Surcharge->version;

            //for version 2 use calculate and request cart2quote for a customisation
            if ((version_compare($version, '1.0.0') >= 0) && version_compare($version, '3.0.0') < 0) {
                if(array_key_exists('surcharge', $collectors)){
                    $collectors['surcharge']->calculate($this);
                }
            }

            //for version 3 to 3.0.29 use 'surcharge' collector
            if ((version_compare($version, '3.0.0') >= 0) && version_compare($version, '3.1.0') < 0) {
                if(array_key_exists('surcharge', $collectors)){
                    $collectors['surcharge']->collect($this);
                }
            }

            //for version >= 3.1.0 'fooman_surcharge' collector
            if ((version_compare($version, '3.1.0') >= 0) && version_compare($version, '4.0.0') < 0) {
                if(array_key_exists('fooman_surcharge', $collectors)){
                    $collectors['fooman_surcharge']->collect($this);
                }
            }
        }

        $collectors['grand_total']->collect($this);

        //fix for based on shipping orgin - part 2 of 2
        $store->setConfig("shipping/origin/country_id", $orgCountry);
        $store->setConfig("shipping/origin/region_id", $orgRegion);
        $store->setConfig("shipping/origin/postcode", $orgPostcode);
        // end fix - part 2 of 2


        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
        // update address table
        if ($this->getAddressId()) {
            $addresses = Mage::helper('qquoteadv/address')->getAddressCollection($this->getData('quote_id'));
            if ($addresses) {
                foreach ($addresses as $address) {
                    if ($address->getData('address_type') == $this->getData('address_type')) {
                        $address->addData($this->getData());
                        $address->save();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Validator for the required minimum order amount (if enabled)
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }

        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        }

        $amount = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        if ($this->getBaseSubtotalWithDiscount() < $amount) {
            return false;
        }
        return true;
    }

    /**
     * Get subtotal amount with applied discount in base currency
     *
     * @return float
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount();
    }

    /**
     * Get subtotal amount with applied discount
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getSubtotal() + $this->getDiscountAmount();
    }

    /**
     * Getter for the shipping description
     *
     * @return mixed
     */
    public function getShippingDescription()
    {
        return $this->getQuote()->getAddressShippingDescription();
    }

    /**
     * Setter for the shipping description
     *
     * @param $desc
     * @return mixed
     */
    public function setShippingDescription($desc)
    {
        return $this->getQuote()->setAddressShippingDescription($desc);
    }

    /**
     * Function to remove all shipping rates from this address
     *
     * @return $this
     */
    public function removeAllShippingRates()
    {
        foreach ($this->getShippingRatesCollection() as $rate) {
            $rate->isDeleted(true);
        }
        return $this;
    }

    /**
     * Get all total amount values
     * Make sure total amounts are calculated
     *
     * @return array
     */
    public function getAllTotalAmounts()
    {
        if(empty($this->_totalAmounts)){
            $this->collectTotals();
        }

        return $this->_totalAmounts;
    }

}
