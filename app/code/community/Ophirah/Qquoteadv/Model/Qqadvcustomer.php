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

class Ophirah_Qquoteadv_Model_Qqadvcustomer extends Mage_Sales_Model_Quote
{
    CONST XML_PATH_QQUOTEADV_PROPOSAL_EXPIRE_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_expire';
    CONST XML_PATH_QQUOTEADV_PROPOSAL_REMINDER_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_reminder';
    CONST XML_PATH_QQUOTEADV_PROPOSAL_ACCEPTED_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_accepted';
    CONST MAXIMUM_AVAILABLE_NUMBER = 99999999;
    protected $_quoteTotal = array();
    protected $_quoteCurrency = null;
    protected $_baseCurrency = null;
    protected $_customer = null;
    protected $_address = null;
    protected $_requestItems = null;
    protected $_weight = null;
    protected $_itemsQty = null;
    protected $_items = null;
    protected $_totalAmounts = null;
    protected $_dataBaseToQuoteRate = 1;

    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvcustomer');
    }

    /**
     * Quote Totals
     * Used in quote backend
     *
     * @return array
     */
    public function setQuoteTotals($quoteTotal)
    {
        $this->_quoteTotal = $quoteTotal;
        return;
    }

    /**
     * Quote Totals
     * Used in quote backend
     *
     * @return array
     */
    public function getQuoteTotals()
    {
        return $this->_quoteTotal;
    }

    /**
     * Calculate Currency Rate from
     * Base => Quote
     *
     * @return int
     */
    public function getBase2QuoteRate()
    {
        if (!$this->getData('currency')) {
            return 1;
        }

        $baseCurrency = Mage::app()->getBaseCurrencyCode();
        $quoteCurrency = $this->getData('currency');

        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrency, $quoteCurrency);
        $b2qRate = (isset($rates[$quoteCurrency])) ? $rates[$quoteCurrency] : 1;

        $this->_dataBaseToQuoteRate = $this->getData('base_to_quote_rate');
        $this->setData('base_to_quote_rate', $b2qRate);

        return $b2qRate;
    }

    /**
     * Create Array with Totals
     * Used in quote backend
     *
     * @param boolean // if $short is 'true' the 'address' and 'items' objects will be left out
     * @return Array
     */
    public function getTotalsArray($short = false)
    {
        $this->getAddressesCollection();


        $this->setQuoteCurrencyCode($this->getCurrency());
        $getTotals = $this->getTotals();
        $totalsArray = array();
        if ($short === true) {
            foreach ($getTotals as $totalCode => $totalData) {
                $newTotalData = new Varien_Object();
                foreach ($totalData->getData() as $k => $v) {
                    if ($k != 'address' && $k != 'items') {
                        $newTotalData->setData($k, $v);
                    }
                }
                $totalsArray[$totalCode] = $newTotalData->getData();
            }
        } else {
            foreach ($getTotals as $totalCode => $totalData) {
                $totalsArray[$totalCode] = $totalData->getData();
            }
        }
        return $totalsArray;
    }

    /**
     * Get all quote totals (sorted by priority)
     * Method process quote states isVirtual and isMultiShipping
     *
     * @return array
     */
    public function getTotals()
    {
        /**
         * If quote is virtual we are using totals of billing address because
         * all items assigned to it
         */
        if ($this->isVirtual()) {
            return $this->getBillingAddress()->getTotals();
        }

        //get 'tax/calculation/based_on' the setting from magento
        $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

        //if it is not billing, fallback to shipping
        if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
            $shippingAddress = $this->getBillingAddress();
            $totals = $shippingAddress->getTotals();
            // Going through all quote addresses and merge their totals
            foreach ($this->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getId() == $shippingAddress->getId()) {
                    continue;
                }
                foreach ($address->getTotals() as $code => $total) {
                    if (isset($totals[$code])) {
                        $newData = $total->getData();
                        foreach ($newData as $key => $value) {
                            if (is_numeric($value)) {
                                $currentValue = $totals[$code]->getData($key);
                                if(!isset($currentValue) || empty($currentValue) || (int)$currentValue == 0 ){
                                    $totals[$code]->setData($key, $value);
                                }
                            }
                        }
                    } else {
                        $totals[$code] = $total;
                    }
                }
            }

            $sortedTotals = array();
            foreach ($this->getShippingAddress()->getTotalModels() as $total) {
                /* @var $total Mage_Sales_Model_Quote_Address_Total_Abstract */
                if (isset($totals[$total->getCode()])) {
                    $sortedTotals[$total->getCode()] = $totals[$total->getCode()];
                }
            }
        } else {
            $shippingAddress = $this->getShippingAddress();
            $totals = $shippingAddress->getTotals();
            // Going through all quote addresses and merge their totals
            foreach ($this->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getId() == $shippingAddress->getId()) {
                    continue;
                }
                foreach ($address->getTotals() as $code => $total) {
                    if (isset($totals[$code])) {
                        $newData = $total->getData();
                        foreach ($newData as $key => $value) {
                            if (is_numeric($value)) {
                                $currentValue = $totals[$code]->getData($key);
                                if(!isset($currentValue) || empty($currentValue) || (int)$currentValue == 0 ){
                                    $totals[$code]->setData($key, $value);
                                }
                            }
                        }
                    } else {
                        $totals[$code] = $total;
                    }
                }
            }

            $sortedTotals = array();
            foreach ($this->getBillingAddress()->getTotalModels() as $total) {
                /* @var $total Mage_Sales_Model_Quote_Address_Total_Abstract */
                if (isset($totals[$total->getCode()])) {
                    $sortedTotals[$total->getCode()] = $totals[$total->getCode()];
                }
            }
        }

        return $sortedTotals;
    }

    /**
     * Event function to trigger before event
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesave', array('quote' => $this));

        return $this;
    }

    /**
     * Event function to trigger after event
     *
     * @return $this
     */
    protected function _afterSave()
    {
        Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersave', array('quote' => $this));
        return $this;
    }

    /**
     * Add quote to qquote_customer table
     * @param array $params quote created information
     * @return mixed
     */
    public function addQuote($params)
    {
        $params['hash'] = $this->getRandomHash(40);
        $this->setData($params);
        $this->addNewAddress();
        $this->save();

        return $this;
    }

    /**
     * Add customer address for the particular quote
     * @param integer $id quote id to be updated
     * @param array $params array of field(s) to be updated
     * @return mixed
     */
    public function addCustomer($id, $params)
    {
        if(!empty($params['shipping_address'])){
            $this->load($id)->addData($params)->setId($id);
            $this->addNewAddress();
            $this->save();
        } else {
            //get the data from the existing user (on email)
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($params['store_id']);
            $customer->loadByEmail($params['email']);
            foreach ($customer->getAddresses() as $addressModel) {
                $params['country_id'] = $addressModel->getCountryId();
                $params['region_id'] = $addressModel->getRegionId();
                $params['shipping_country_id'] = $addressModel->getCountryId();
                $params['shipping_region_id'] = $addressModel->getRegionId();
                break; //just one address
            }

            $this->load($id)->addData($params)->setId($id);
            $this->addNewAddress();
            $this->save();
        }

        return $this;
    }

    /**
     * Check if email allready exists
     * If not, create new account
     * 
     * @param   array       // customer data
     * @return  object      // Mage_Customer_Model_Customer
     */
    public function checkCustomer($params)
    {
        // Params
        if (!isset($params['website_id'])) {
            $params['website_id'] = Mage::app()->getStore()->getWebsiteId();
        }
        try {
            if (!Zend_Validate::is($params['email'], 'EmailAddress')) {

                // TODO: Create action to do if email address is invalid
                //notice that we use the translation from 'newsletter'
                //so this sentence is always translated by default Magento translation files
                Mage::throwException(Mage::helper('newsletter')->__('Please enter a valid email address.'));
            }

            if (Mage::helper('qquoteadv')->userEmailAlreadyExists($params['email'])) {
                $this->_isEmailExists = true;
                // TODO: make 'update current address':
                // Set action to do if customer exists
                // Adding customer address if customer
                // already exists
                $customer = Mage::getModel('customer/customer')->setWebsiteId($params['website_id'])->loadByEmail($params['email']);
                $address = Mage::helper('qquoteadv/address')->buildAddress($params);

                // Add address information to quote
                foreach ($address as $key => $updateData) {
                    $customer->setData($key, $updateData);
                }

                // Check if address allready exists
                $addressFound = false;
                foreach ($customer->getAddresses() as $checkAddress) {
                    if ($checkAddress->getData('country_id') == $customer->getData('country_id') &&
                        $checkAddress->getData('postcode') == $customer->getData('postcode') &&
                        $checkAddress->getData('street') == $customer->getData('street')
                    ) {
                        $addressFound = true;
                    }
                }

                // Add new address
                if ($addressFound === false) {
                    $vars['saveAddressBook'] = 1;
                    $vars['defaultShipping'] = (!$customer->getDefaultShipping()) ? 1 : 0;
                    $vars['defaultBilling'] = (!$customer->getDefaultBilling()) ? 1 : 0;

                    Mage::helper('qquoteadv/address')->addQuoteAddress($customer, $address['billing'], $vars);
                }

            } else {
                // create new account                
                $customer = $this->_createNewCustomerAccount($params);

                // Set address
                $address = Mage::helper('qquoteadv/address')->buildAddress($params);

                foreach ($address as $key => $updateData) {
                    $customer->setData($key, $updateData);
                }

                $vars['saveAddressBook'] = 1;
                $vars['defaultShipping'] = 1;
                $vars['defaultBilling'] = 1;

                Mage::helper('qquoteadv/address')->addQuoteAddress($customer, $address['billing'], $vars);
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }

        return $customer;
    }

    /**
     * Create new customer account
     * 
     * @param   array       // Customer account params
     * @return  object      // Mage_Customer_Model_Customer
     */
    protected function _createNewCustomerAccount($params)
    {
        $password_test = $this->_generatePassword(7);
        $is_subscribed = 0;

        //# NEW USER REGISTRATION
        if ($params['email'] && !$params['logged_in'] === true) {
            $cust = Mage::getModel('customer/customer');
            $cust->setWebsiteId($params['website_id'])->loadByEmail($params['email']);

            //#create new user
            if (!$cust->getId()) {
                $customerData = array(
                    'firstname' => $params['firstname'],
                    'lastname' => $params['lastname'],
                    'email' => $params['email'],
                    'password' => $password_test,
                    'password_hash' => md5($password_test),
                    'is_subscribed' => $is_subscribed,
                    'new_account' => true
                );

                $customer = Mage::getModel('qquoteadv/customer_customer');
                $customer->setWebsiteId($params['website_id']);
                $customer->setData($customerData);
                $customer->save();
            }
        }

        return $customer;
    }

    /**
     * Update Quote
     *
     * @param integer $id
     * @param aray $params
     * @return \Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function updateQuote($id, $params)
    {
        $this->load($id)
            ->setData($params)
            ->setId($id);
        $this->save();

        return $this;
    }

    public function getStoreGroupName()
    {
        $storeId = $this->getStoreId();
        if (is_null($storeId)) {
            return $this->getStoreName(1); // 0 - website name, 1 - store group name, 2 - store name
        }
        return $this->getStore()->getGroup()->getName();
    }

    /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($storeId = $this->getStoreId()) {
            return Mage::app()->getStore($storeId);
        }
        return Mage::app()->getStore();
    }


    /**
     * Get formated quote created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format);
    }

    /**
     * Get formated quote created date in given format
     *
     * @param   string $format date format
     * @return  string
     */
    public function getCreatedAtInFormat($format = 'm/d/Y')
    {
        return Mage::getModel('core/date')->date($format, $this->getCreatedAt());
    }

    /**
     * Get formated quote expire date in store timezone
     * Additionally show how many days a proposal is valid
     *
     * @param $format
     * @param bool $showRemainingDays
     * @return string
     */
    public function getExpireAtFormated($format, $showRemainingDays = false)
    {
        if($showRemainingDays){
            $proposalDate = $this->getCreatedAt();
            $expiryDate = $this->getExpiry();

            if($expiryDate){
                $expDays = (int)round((date_create($expiryDate)->format("U") - date_create($proposalDate)->format("U")) / (60 * 60 * 24));
            } else {
                $expDays = (int)Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal', $this->getStoreId());
            }

            if ($expDays) {
                $date = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
                $note = "( " . Mage::helper('qquoteadv')->__("%s days", $expDays) . " )";
                return Mage::helper('core')->formatDate($date, $format) . ' ' . $note;
            }
        }

        return Mage::helper('core')->formatDate($this->getExpiry(), $format);
    }

    /**
     * Get formated quote expire date in given format
     *
     * @param   string $format date format
     * @return  string
     */
    public function getExpireAtInFormat($format = 'm/d/Y')
    {
        return Mage::getModel('core/date')->date($format, $this->getExpiry());
    }

    /**
     * Get Address formatted for html
     * @return string
     */
    public function getBillingAddressFormatted($type = 'html')
    {
        return $this->getBillingAddress()->format($type);
    }

    /**
     * Get Address formatted for html
     * @return string
     */
    public function getShippingAddressFormatted($type = 'html')
    {
        return $this->getShippingAddress()->format($type);
    }

    public function getBaseToQuoteRate()
    {
        $currency = Mage::getModel('directory/currency');
        $currency->setData('currency_code', Mage::getStoreConfig('currency/options/base'));
        if ($this->getData('currency')) {
            return $currency->getRate($this->getData('currency'));
        } else {
            return 1;
        }
    }

    /**
     * Get Shipping Methods formatted for html
     * @return string
     */
    public function getShippingMethodsFormatted()
    {
        // Get Shipping Methods
        $shippingRates = Mage::getModel('qquoteadv/quoteshippingrate')->getShippingRatesList($this);
        $shippingRateList = $shippingRates['shippingList'];

        // Draw Shipping Rates
        $str = "";
        foreach ($shippingRateList as $k => $v) {
            // Draw Carrier Title
            $str .= '<span style="font-weight:bold;line-height:2em;">' . $k . '</span><br />';
            foreach ($v as $rate) {
                $price = $this->formatPrice($this->getBaseToQuoteRate() * $rate['price']);
                $str .= '<span style="margin-left: 10px;">' . uc_words($rate['method_list']) . " -  <b>" . $price . "</b></span><br />";
            }
        }

        return $str; //$this->_formatAddress($str); 
    }


    // function to get variables in email templates
    // if $var is allowed, it's value will be returned
    public function getVariable($var)
    {
        $allowed_var = array(
            "created_at",
            "updated_at",
            "is_quote",
            "prefix",
            "firstname",
            "middlename",
            "lastname",
            "suffix",
            "company",
            "email",
            "country_id",
            "region",
            "region_id",
            "city",
            "address",
            "postcode",
            "telephone",
            "fax",
            "client_request",
            "shipping_type",
            "increment_id",
            "shipping_prefix",
            "shipping_firstname",
            "shipping_middlename",
            "shipping_lastname",
            "shipping_suffix",
            "shipping_company",
            "shipping_country_id",
            "shipping_region",
            "shipping_region_id",
            "shipping_city",
            "shipping_address",
            "shipping_postcode",
            "shipping_telephone",
            "shipping_fax",
            "imported",
            "currency",
            "expiry",
            "shipping_description",
            "address_shipping_description",
            "address_shipping_method"
        );

        if (in_array($var, $allowed_var)) {
            return $this->getData($var);
        }

        return null;
    }


    public function getFullPath()
    {

        $valid = Mage::helper('qquoteadv')->isValidHttp($this->getPath());
        $path = $this->getPath(); //urlencode($this->getPath());
        if ($valid) {
            return $path;
        } else {
            return self::getUploadPath(array('dir' => $this->getData('quote_id'), 'file' => $path));
        }
    }

    public function getUploadPath($filePath = NULL)
    {

        if (Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId())) {
            $fileUpload = Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId());
        } else {
            $fileUpload = 'qquoteadv';
        }

        if ($filePath != NULL) {
            if (is_array($filePath)) {
                $fileUpload .= DS . $filePath['dir'] . DS . $filePath['file'];
            } else {
                $fileUpload .= DS . $filePath;
            }
        }

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $fileUpload;

    }

    public function getUploadDirPath($filePath = NULL)
    {

        if (Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId())) {
            $fileUpload = Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId());
        } else {
            $fileUpload = 'qquoteadv'; // default value
        }

        if ($filePath != NULL) {
            $fileUpload .= DS . $filePath;
        }

        return Mage::getBaseDir('media') . DS . $fileUpload;

    }


    public function sendExpireEmail()
    {
        $expireTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_expire', $this->getStoreId());
        $expiredQuotes = $this->getCollection()
            ->addFieldToFilter('status', array('in' => array(50, 53)))
            ->addFieldToFilter('no_expiry', array('eq' => 0))
            ->addFieldToFilter('expiry', array('eq' => date('Y-m-d')));

        foreach ($expiredQuotes as $expiredQuote) {
            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($expiredQuote->getData('quote_id'));

            $vars['quote'] = $_quoteadv;
            $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel();
            $disabledEmail = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;
            if ($template != $disabledEmail){
                if (is_numeric($expireTemplateId)) {
                    $template->load($expireTemplateId);
                } else {
                    $template->loadDefault($expireTemplateId);
                }

                $sender = $this->getEmailSenderInfo();
                $template->setSenderName($sender['name']);
                $template->setSenderEmail($sender['email']);

                $subject = $template['template_subject'];
                $template->setTemplateSubject($subject);

                $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
                if ($bcc) {
                    $bccData = explode(";", $bcc);
                    $template->addBcc($bccData);
                }

                if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())) {
                    $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
                }

                /**
                 * Opens the qquote_request.html, throws in the variable array
                 * and returns the 'parsed' content that you can use as body of email
                 */
                $area = Mage::getDesign()->getArea();
                $package = Mage::getDesign()->getPackageName();
                $theme = Mage::getDesign()->getTheme($area);
                Mage::getDesign()->setArea('frontend')->setPackageName('base')->setTheme('default');
                $template->getProcessedTemplate($vars);
                Mage::getDesign()->setArea($area)->setPackageName($package)->setTheme($theme);

                /*
                 * getProcessedTemplate is called inside send()
                 */
                $template->setData('c2qParams', array('email' => $_quoteadv->getEmail(), 'name' => $_quoteadv->getFirstname()));
                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
                $res = $template->send($_quoteadv->getEmail(), $_quoteadv->getFirstname(), $vars);
                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

            }

            // update quote status
            $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));

        }

    }

    public function sendReminderEmail()
    {
        if (Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/send_reminder') > 0) {

            $reminderTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_reminder', $this->getStoreId());
            if ($reminderTemplateId) {
                $templateId = $reminderTemplateId;
            } else {
                $templateId = self::XML_PATH_QQUOTEADV_PROPOSAL_REMINDER_EMAIL_TEMPLATE;
            }

            $reminderQuotes = $this->getCollection()
                ->addFieldToFilter('status', array('in' => array(50, 52, 53)))
                ->addFieldToFilter('no_reminder', array('eq' => 0))
                ->addFieldToFilter('reminder', array('eq' => date('Y-m-d')));

            foreach ($reminderQuotes as $_quoteadv) {
                if (substr($_quoteadv->getData('proposal_sent'), 0, 4) != 0) {
                    $vars['quote'] = $_quoteadv;
                    $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

                    $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel();

                    // get locale of quote sent so we can sent email in that language	
                    $storeLocale = Mage::getStoreConfig('general/locale/code', $_quoteadv->getStoreId());

                    if (is_numeric($templateId)) {
                        $template->load($templateId);
                    } else {
                        $template->loadDefault($templateId, $storeLocale);
                    }

                    $sender = $_quoteadv->getEmailSenderInfo();
                    $template->setSenderName($sender['name']);
                    $template->setSenderEmail($sender['email']);

                    $subject = $template['template_subject'];
                    $template->setTemplateSubject($subject);

                    $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
                    if ($bcc) {
                        $bccData = explode(";", $bcc);
                        $template->addBcc($bccData);
                    }

                    if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())) {
                        $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
                    }

                    /**
                     * Opens the qquote_request.html, throws in the variable array
                     * and returns the 'parsed' content that you can use as body of email
                     */
                    $area = Mage::getDesign()->getArea();
                    $package = Mage::getDesign()->getPackageName();
                    $theme = Mage::getDesign()->getTheme($area);
                    Mage::getDesign()->setArea('frontend')->setPackageName('base')->setTheme('default');
                    $template->getProcessedTemplate($vars);
                    Mage::getDesign()->setArea($area)->setPackageName($package)->setTheme($theme);

                    /*
                     * getProcessedTemplate is called inside send()
                     */
                    $template->setData('c2qParams', array('email' => $_quoteadv->getEmail(), 'name' => $_quoteadv->getFirstname()));
                    Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
                    $res = $template->send($_quoteadv->getEmail(), $_quoteadv->getFirstname(), $vars);
                    Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));
                }

            }
        }
    }

    public function sendQuoteAccepted($quoteId)
    {

        $acceptedTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_accepted', $this->getStoreId());
        $disabledEmail = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;
        if ($acceptedTemplateId != $disabledEmail){
            if ($acceptedTemplateId) {
                $templateId = $acceptedTemplateId;
            } else {
                $templateId = self::XML_PATH_QQUOTEADV_PROPOSAL_ACCEPTED_EMAIL_TEMPLATE;
            }

            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);

            $vars['quote'] = $_quoteadv;
            $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel();

            // get locale of quote sent so we can sent email in that language
            $storeLocale = Mage::getStoreConfig('general/locale/code', $_quoteadv->getStoreId());

            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->loadDefault($templateId, $storeLocale);
            }

            $sender = $_quoteadv->getEmailSenderInfo();
            $template->setSenderName($sender['name']);
            $template->setSenderEmail($sender['email']);
            $vars['adminname'] = $sender['name'];

            $subject = $template['template_subject'];
            $template->setTemplateSubject($subject);

            $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
            if ($bcc) {
                $bccData = explode(";", $bcc);
                $template->addBcc($bccData);
            }

            if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())) {
                $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
            }

            /**
             * Opens the qquote_request.html, throws in the variable array
             * and returns the 'parsed' content that you can use as body of email
             */
            $area = Mage::getDesign()->getArea();
            $package = Mage::getDesign()->getPackageName();
            $theme = Mage::getDesign()->getTheme($area);
            Mage::getDesign()->setArea('frontend')->setPackageName('base')->setTheme('default');
            $template->getProcessedTemplate($vars);
            Mage::getDesign()->setArea($area)->setPackageName($package)->setTheme($theme);

            //Uncomment this to enable attachments in the proposal confirmed email
//            //is pdf or doc attached bools
//            $vars['attach_pdf'] = $vars['attach_doc'] = false;
//
//            //Create pdf to attach to email
//            if (Mage::getStoreConfig('qquoteadv_quote_emails/attachments/pdf', $_quoteadv->getStoreId())) {
//                $_quoteadv->_saveFlag = true;
//
//                //totals need to be collected before generating the pdf (until we save the totals in the database)
//                $_quoteadv->collectTotals();
//
//                $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($_quoteadv);
//                $_quoteadv->_saveFlag = false;
//                $realQuoteadvId = $_quoteadv->getIncrementId() ? $_quoteadv->getIncrementId() : $_quoteadv->getId();
//                try {
//                    $file = $pdf->render();
//                    $name = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);
//                    $template->getMail()->createAttachment($file, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name . '.pdf');
//                    $vars['attach_pdf'] = true;
//                } catch (Exception $e) {
//                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
//                }
//
//            }
//
//            //Check if attachment needs to be sent with email
//            if ($doc = Mage::getStoreConfig('qquoteadv_quote_emails/attachments/doc', $_quoteadv->getStoreId())) {
//                $pathDoc = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'quoteadv' . DS . $doc;
//                try {
//                    $file = file_get_contents($pathDoc);
//                    $mimeType = Mage::helper('qquoteadv/file')->getMimeType($pathDoc);
//
//                    $info = pathinfo($pathDoc);
//                    //$extension = $info['extension'];
//                    $basename = $info['basename'];
//                    $template->getMail()->createAttachment($file, $mimeType, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $basename);
//                    $vars['attach_doc'] = true;
//                } catch (Exception $e) {
//                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
//                }
//            }

            /*
             * getProcessedTemplate is called inside send()
             */
            $template->setData('c2qParams', $sender);
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
            $res = $template->send($sender['email'], $sender['name'], $vars);
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

        }

    }

    public function exportQuotes($qquoteIds, $filePath)
    {

        $csv_titles = array(
            "id",
            "timestamp",
            "name",
            "address",
            "zipcode",
            "city",
            "country",
            "phone",
            "email",
            "remarks",
            "product id",
            "product name",
            "product attributes",
            "quantity",
            "product sku"
        );

        $file = fopen($filePath, 'w'); //open, truncate to 0 and create if doesnt exist

        if (!$this->writeCsvRow($csv_titles, $file)) return false;

        foreach ($qquoteIds as $qquoteId) {
            $qquote = $this->load($qquoteId);

            $quoteId = $qquote->getQuoteId();
            $timestamp = $qquote->getCreatedAt();

            // build name
            $nameArr = array();
            if ($qquote->getPrefix()) array_push($nameArr, $qquote->getPrefix());
            if ($qquote->getFirstname()) array_push($nameArr, $qquote->getFirstname());
            if ($qquote->getMiddlename()) array_push($nameArr, $qquote->getMiddlename());
            if ($qquote->getLastname()) array_push($nameArr, $qquote->getLastname());
            if ($qquote->getSuffix()) array_push($nameArr, $qquote->getSuffix());
            $name = join($nameArr, " ");
            $email = $qquote->getEmail();
            $city = $qquote->getCity();
            $address = $qquote->getData('address');
            $postcode = $qquote->getPostcode();
            $tel = $qquote->getTelephone();
            $country = $qquote->getCountryId();
            $remarks = $qquote->getClientRequest();

            $collection = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteProduct($quoteId);

            $basicFields = array(
                $quoteId, $timestamp, $name, $address, $postcode,
                $city, $country, $tel, $email, $remarks
            );

            foreach ($collection as $item) {
                $baseProductId = $item->getProductId();
                $productObj = Mage::getModel('catalog/product')->load($baseProductId);

                $productName = $productObj->getName();
                $productAttributes = "";

                $productObj->setStoreId($item->getStoreId() ? $item->getStoreId() : 1);
                $quoteByProduct = Mage::helper('qquoteadv')->getQuoteItem($productObj, $item->getAttribute());

                foreach ($quoteByProduct->getAllItems() as $_unit) {

                    if ($_unit->getProductId() == $productObj->getId()) {
                        if ($_unit->getProductType() == "bundle") {
                            $_helper = Mage::helper('bundle/catalog_product_configuration');
                            $_options = $_helper->getOptions($_unit);
                        } else {
                            $_helper = Mage::helper('catalog/product_configuration');
                            $_options = $_helper->getCustomOptions($_unit);
                        }

                        foreach ($_options as $option) {
                            if (is_array($option['value'])) $option['value'] = implode(",", $option['value']);
                            $productAttributes .= $option['label'] . ":" . strip_tags($option['value']);
                            $productAttributes .= "|";
                        }
                    }
                }
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                $requestItem = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('product_id', $baseProductId)
                    ->getFirstItem();


                $qty = $requestItem->getRequestQty();
                $SKU = $productObj->getSku();

                $productFields = array($baseProductId, $productName, $productAttributes, $qty, $SKU);

                $fields = array_merge($basicFields, $productFields);

                if (!$this->writeCsvRow($fields, $file)) {
                    Mage::log('Exception: ' ."could not write:", null, 'c2q_exception.log', true);
                    Mage::log($fields, null, 'c2q_exception.log', true);
                    return false;
                }
            }
        }
        return true;
    }

    public function writeCsvRow($row, $filePointer)
    {
        if (is_array($row)) $row = '"' . implode('","', $row) . '"';
        $row = $row . "\n";
        try {
            fwrite($filePointer, $row);
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        }
        return true;
    }

    public function getRandomHash($length = 40)
    {
        $max = ceil($length / 40);
        $random = '';
        for ($i = 0; $i < $max; $i++) {
            $random .= sha1(microtime(true) . mt_rand(10000, 90000));
        }
        return substr($random, 0, $length);
    }

    public function getUrlHash()
    {

        if ($this->getHash() == "") {
            $hash = $this->getRandomHash();
            $this->setHash($hash);
            $this->save();
        }

        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        $hash = sha1($customer->getEmail() . $this->getHash() . $customer->getPasswordHash());
        return $hash;
    }

    public function getQuoteCurrency()
    {
        if (is_null($this->_quoteCurrency)) {
            $this->_quoteCurrency = Mage::getModel('directory/currency')->load($this->getCurrency());
        }
        return $this->_quoteCurrency;
    }

    public function isCurrencyDifferent()
    {
        return $this->getQuoteCurrency() != $this->getBaseCurrencyCode();
    }

    public function getBaseCurrencyCode()
    {

        return Mage::app()->getBaseCurrencyCode();
    }

    public function getBaseCurrency()
    {
        if (is_null($this->_baseCurrency)) {
            $this->_baseCurrency = Mage::getModel('directory/currency')->load($this->getBaseCurrencyCode());
        }
        return $this->_baseCurrency;
    }


    public function formatBasePrice($price)
    {
        return $this->formatBasePricePrecision($price, 2);
    }

    public function formatBasePricePrecision($price, $precision)
    {
        return $this->getBaseCurrency()->formatPrecision($price, $precision);
    }

    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getQuoteCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    // we do not quote for virtual items
    public function getVirtualItemsQty()
    {
        return 0;
    }

    /**
     * Add new addresses to quote
     * in database
     */
    public function addNewAddress()
    {
        $helper = Mage::helper('qquoteadv/address');
        // check for existing data
        if ($helper->getAddressCollection($this->getData('quote_id'))) {
            $this->updateAddress();
        } else {
            // Get addresses from quote       
            if ($addresses = $helper->getAddresses($this)) {
                foreach ($addresses as $address) {
                    $helper->addAddress($this->getData('quote_id'), $address, true);
                }
            }
        }
    }

    /**
     * Update Quote addresses
     * in database
     */
    public function updateAddress()
    {
        // Update addresses associated to the quote
        Mage::helper('qquoteadv/address')->updateAddress($this);
    }

    /**
     * Function to get the address for a quote based on the given type
     * If no type is given, the type is set by the Magento setting: 'tax/calculation/based_on'
     *
     * @param null $type
     * @return Mage_Core_Model_Abstract|null
     */
    public function getAddress($type = null)
    {
        //if no type is given, get the type from the Magento settings
        if ($type == null) {
            //get 'tax/calculation/based_on' the setting from magento
            $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

            //if it is not billing, fallback to shipping
            if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
            } else {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
            }
        }

        //check if this address is already loaded (speed improvement)
        if ($this->_address == null || ($this->_address->getData('address_type') != $type)) {
        //if ($this->_address == null){
            $this->_address = Mage::getSingleton('qquoteadv/address');

            //collect the quote addresses
            $addresses = Mage::helper('qquoteadv/address')->buildQuoteAdresses($this);

            //check for each address if it is the requested type
            foreach ($addresses as $address) {
                if ($address->getData('address_type') == $type) {
                    // Set Address to quote
                    $this->addData($address->getData());

                    // Set Address to address
                    $this->_address->addData($address->getData());
                }
            }

            //set this quote to the current address
            $this->_address->setQuote($this);
        }

        return $this->_address;
    }

    /**
     * Function to get only the address(es) object for a quote
     *
     * @param null $type
     * @return mixed
     */
    public function getAddressRaw($type = null){
        //if no type is given, get the type from the Magento settings
        if ($type == null) {
            //get 'tax/calculation/based_on' the setting from magento
            $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

            //if it is not billing, fallback to shipping
            if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
            } else {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
            }
        }

        //create address model
        $rawAddress = Mage::getModel('qquoteadv/address');

        //collect the quote addresses
        $addresses = Mage::helper('qquoteadv/address')->buildQuoteAdresses($this);

        //check for each if it is the request type
        foreach ($addresses as $address) {
            if ($address->getData('address_type') == $type) {
                // Set Address to address
                $rawAddress->addData($address->getData());
                $rawAddress->setQuote($this);
                return $rawAddress;
            }
        }

        //return empty model if no result is found
        return $rawAddress;
    }

    /**
     * Fix for seperate shipping address
     * with prefix and 'address / street' naming
     * @return string
     */
    public function getShippingStreets()
    {
        return $this->getData('shipping_address');
    }

    /**
     * Retrieve Shipping Address
     * @return object Ophirah_Qquoteadv_Model_Address
     */
    public function getShippingAddress()
    {
        return $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING);
    }

    public function getShippingAddressRaw()
    {
        return $this->getAddressRaw(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING);
    }

    /**
     * Retrieve Billing Address
     * @return object Ophirah_Qquoteadv_Model_Address
     */
    public function getBillingAddress()
    {
        return $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING);
    }

    public function getBillingAddressRaw()
    {
        return $this->getAddressRaw(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING);
    }

    /**
     * Retrieve customer address info
     * by type
     *
     * @param string $type
     * @return array
     */
    public function getAddressInfoByType($type)
    {
        return Mage::helper('qquoteadv/address')->getAddressInfoByType($this->getData('quote_id'), $type);
    }

    /**
     * Retrieve quote address collection
     *
     * @return array        // Mage_Sales_Model_Quote_Address
     */
//    public function getAddressesCollection()
//    {
//        if (is_null($this->_addresses)) {
//            // Load only one address
//            if (Mage::getStoreConfig('tax/calculation/based_on') == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
//                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
//            } else {
//                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
//            }
//            $this->_addresses[] = $this->getAddress($type);
//
//            // Assign quote to the addresses
//            if ($this->getId()) {
//                foreach ($this->_addresses as $address) {
//                    $address->setQuote($this);
//                }
//            }
//        }
//        return $this->_addresses;
//    }
    /**
     * Retrieve quote address collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = Mage::getModel('qquoteadv/address')->getCollection()->setQuoteFilter($this->getId());

            // Assign quote to the addresses
            if ($this->getId()) {
                foreach ($this->_addresses as $address) {
                    $address->setQuote($this);
                }
            }
        }
        return $this->_addresses;
    }

    /**
     * Collect Quote Totals
     *
     * @return \Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function collectTotals()
    {

        if ($this->getTotalsCollectedFlag()) {
            return $this;
        }

        //get 'tax/calculation/based_on' the setting from magento
        $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

        //if it is not billing, fallback to shipping
        if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
            $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
        } else {
            $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
        }

        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));

        $addresses = $this->getAllAddresses();
        foreach ($addresses as $address) {
            $this->setSubtotal(0);
            $this->setBaseSubtotal(0);
            $this->setGrandTotal(0);
            $this->setBaseGrandTotal(0);

            $this->setTaxAmount(0);
            $this->setBaseTaxAmount(0);
            $this->setSubtotalInclTax(0);
            $this->setBaseSubtotalInclTax(0);
            $this->setBaseShippingAmountInclTax(0);
            $this->setShippingAmountInclTax(0);
            $this->setBaseShippingInclTax(0);
            $this->setShippingInclTax(0);
            $this->setShippingAmount(0);
            $this->setBaseShippingAmount(0);
            $this->setShipping(0);
            $this->setBaseShipping(0);

            $address->setTotalAmount('subtotal', 0);
            $address->setBaseTotalAmount('subtotal', 0);
            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);
            $address->setTotalAmount('tax', 0);
            $address->setBaseTotalAmount('tax', 0);
            $address->setSubtotalInclTax(0);
            $address->setBaseSubtotalInclTax(0);
            $address->setBaseShippingInclTax(0);
            $address->setShippingInclTax(0);
            $address->setBaseShippingInclTax(0);
            $address->setShippingInclTax(0);
            $address->setShippingAmount(0);
            $address->setShippingAmount(0);
            $address->setShippingAmount(0);
            $address->setBaseShippingAmount(0);
            $this->setItemsCount(0);
            $this->setItemsQty(0);
            $this->save();

            //fooman surcharge compatible
            if(!Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')) {
	        
	            //Only collect what is needed ( speed improvement )
	            if($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
	                $address->collectTotals();
	            } else {
	                if ($address->getData('address_type') == $type) {
	                    $address->collectTotals();
	                }
	            }

	        } else {

	        	$address->collectTotals();

	        }

            $this->setSubtotal($address->getTotalAmount('subtotal'));
            $this->setBaseSubtotal($address->getBaseTotalAmount('subtotal'));
            $this->setGrandTotal($address->getGrandTotal());
            $this->setBaseGrandTotal($address->getBaseGrandTotal());
            $this->setTaxAmount($address->getTotalAmount('tax'));
            $this->setBaseTaxAmount($address->getBaseTotalAmount('tax'));
            $this->setSubtotalInclTax($address->getSubtotalInclTax());
            $this->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax());
            $this->setBaseShippingAmountInclTax($address->getBaseShippingInclTax());
            $this->setShippingAmountInclTax($address->getShippingInclTax());
            $this->setBaseShippingInclTax($address->getBaseShippingInclTax());
            $this->setShippingInclTax($address->getShippingInclTax());
            $this->setShippingAmount($address->getShippingAmount());
            $this->setBaseShippingAmount($address->getBaseShippingAmount());
            $this->setShipping($address->getShippingAmount());
            $this->setBaseShipping($address->getBaseShippingAmount());
            $this->checkQuoteAmount($this->getGrandTotal());
            $this->checkQuoteAmount($this->getBaseGrandTotal());
            $this->_totalsCollected = $address;

            //fooman surcharge compatible
            if(!Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')){
                //if fooman surcharge is not installed, this is safe
                Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
            } else {
                $version = (int)Mage::getConfig()->getNode()->modules->Fooman_Surcharge->version;

                if($version > 1 && $version < 3){
                    //for version 2 this must be disabled
                    //Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
                }

                if($version > 2 && $version < 4){
                    //for version 3 this is safe
                    Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
                }
            }

                //update and return calculated type
                if ($address->getData('address_type') == $type) {
                    $this->updateAddress($this);
                    $return = $this;
                }
        }
        $this->setTotalsCollectedFlag(true);

        if(isset($return) && !empty($return)){
            $return->setTotalsCollectedFlag(true);
            return $return;
        }
        return $this;
    }

    /**
     * If fixed Quote Total is given
     * recalculate custom item prices
     *
     * @param float $recalPrice
     * @return boolean
     */
    public function recalculateFixedPrice($recalPrice = array())
    {

        // Declare price types
        $recalValue = null;
        $recalType = null;
        $recalPriceTypes = array('fixed' => 1, 'percentage' => 2);

        // Get price type to handle
        foreach ($recalPrice as $k => $v) {
            if ((int)trim($v) != null) {
                $recalType = $recalPriceTypes[$k];
                $recalValue = (int)$v;
            }
        }

        // Make sure all variables are set
        if ($recalType == null || $recalValue == null || !is_numeric($recalValue)) {
            return false;
        }

        // Collect current Totals
        $currentTotals = $this->getAddress()->getAllTotalAmounts();
        if (!$currentTotals || (!$this->getData('orgBasePrice') && !$this->getData('orgFinalBasePrice'))) {
            return false;
        }

        // Get Base to Quote Rate
        $b2qRate = $this->getBase2QuoteRate($this->getData('currency'));

        // Get current Items
        $requestItems = Mage::getSingleton('qquoteadv/requestitem')->getCollection()->setQuote($this);

        try {
            if ($requestItems){

                if ($recalType == 1) { // Fixed

                    // Setting variables
                    $itemCount = count($requestItems);
                    $count = 1;
                    $restBasePrice = (float)0;
                    $expectedBasePrice = (float)0;
                    $deltaMax = (float)0.001;
                    $useExpectedPrice = false;

                    $fixedBasePrice = round($recalPrice['fixed'], 2) / $b2qRate;

                    //neutralize VAT/TAX difference based on first product in the quote
                    $store = Mage::app()->getStore($this->getStoreId());
                    // Item Original Price
                    if(Mage::getStoreConfig('tax/calculation/price_includes_tax', $store)){
                        //get the tax rate for the defailt store and remove it
                        $taxCalculation = Mage::getModel('tax/calculation');
                        $request = $taxCalculation->getRateRequest($this->_address, null, null, Mage::app()->getStore());

                        foreach ($requestItems as $item) {
                            $firstProduct = Mage::getModel('catalog/product')->load($item->getProductId());
                            break;
                        }

                        $taxClassId = $firstProduct->getTaxClassId();
                        $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
                        $fixedBasePrice = $fixedBasePrice / ((100+$percent)/100);

                        //add the tax rate from the current store
                        $request = $taxCalculation->getRateRequest($this->_address, null, null, $store);
                        $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
                        $fixedBasePrice = $fixedBasePrice * ((100+$percent)/100);
                    }

                    //if available use final base price
                    $orgFinalBasePrice = $this->getData('orgFinalBasePrice') + $this->getData('orgSpecialPrices');
                    if(isset($orgFinalBasePrice) && !empty($orgFinalBasePrice) && ($orgFinalBasePrice != 0)){
                        $totalOrgRatio = $fixedBasePrice / ($orgFinalBasePrice);
                    } else {
                        $totalOrgRatio = $fixedBasePrice / ($this->getData('orgBasePrice'));
                    }

                    foreach ($requestItems as $item) {
                        // Last item gets custom price calculated
                        // from difference between fixedTotal and
                        // current custom price subtotal
                        if ($count == $itemCount) {
                            if ($item->getData('request_qty') > 0) {
                                $expectedBasePrice = (float)($fixedBasePrice - $restBasePrice) / $item->getData('request_qty');
                                $expectedBasePrice = round($expectedBasePrice, 2);
                                $expectedDelta = $expectedBasePrice - ($item->getData('original_price') * $totalOrgRatio);

                                if ($expectedDelta < 0) { // Create positive delta value
                                    $expectedDelta = -1 * $expectedDelta;
                                }
                                // check the expected price is within error margin
                                if ($expectedDelta < $deltaMax) {
                                    $useExpectedPrice = true;
                                }
                            }
                        }

                        if ($useExpectedPrice === true) {
                            $item->setData('owner_base_price', ($expectedBasePrice));
                            $item->setData('owner_cur_price', $expectedBasePrice * $this->_dataBaseToQuoteRate);
                        } else {
                            $item->setData('owner_base_price', $item->getData('original_price') * $totalOrgRatio);
                            $item->setData('owner_cur_price', $item->getData('original_price') * $totalOrgRatio * $this->_dataBaseToQuoteRate);
                        }

                        $restBasePrice += $item->getData('request_qty') * ($item->getData('original_price') * $totalOrgRatio);

                        $count++;
                    }

                    $requestItems->save();

                } elseif ($recalType == 2) { // Percentage

                    // Setting variables
                    $totalOrgRatio = (100 - $recalValue) / 100;

                    foreach ($requestItems as $item) {
                        $item->setData('owner_base_price', $item->getData('original_price') * $totalOrgRatio);
                        $item->setData('owner_cur_price', $item->getData('original_price') * $totalOrgRatio * $this->_dataBaseToQuoteRate);
                    }

                    $requestItems->save();

                } else {
                    return false;
                }

            }

        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        }

        return true;
    }

    /**
     * Calculate Quote reduction
     * from stored quote data
     * See Ophirah_Qquoteadv_Model_Quote_Total_C2qtotal
     * and Ophirah_Qquoteadv_Model_Observer
     *
     * @return boolean / float reduction
     */
    public function getQuoteReduction()
    {
        $rate = $this->getBase2QuoteRate();
        $orgBasePrice = $this->getAddress()->getQuote()->getData('orgFinalBasePrice') * $rate;
        $quoteFinalPrice = $this->getAddress()->getQuote()->getData('quoteFinalBasePrice') * $rate;
        $reduction = $orgBasePrice - $quoteFinalPrice;

        if ($reduction > 0) {
            return $reduction;
        }

        return false;
    }

    public function getQuoteProfit()
    {
        $rate = $this->getBase2QuoteRate();
        $orgCostPrice = $this->getAddress()->getQuote()->getData('quoteBaseCostPrice') * $rate;
        $quoteFinalPrice = $this->getAddress()->getQuote()->getData('quoteFinalBasePrice') * $rate;
        $profit = $quoteFinalPrice - $orgCostPrice;

        return $profit;
    }

    /**
     * @return boolean|Array
     *
     */
    public function getAllRequestItemsForCart()
    {
        $returnValue = array();

        if ($this->_requestItems != null) {
            $requestItems = Mage::getSingleton('qquoteadv/requestitem')->getCollection()->setQuote($this);

            foreach ($requestItems as $item) {
                $returnValue[$item->getQuoteadvProductId()] = $item->getId();
            }
        }

        return $returnValue;
    }

    /**
     * Add requested products to the object.
     * addQuoteProductAdvanced() method customized
     * core addProductAdvanced() method
     *
     * @return  object      //quote items in $this->_requestedItems
     */
    public function getAllRequestItems()
    {
        if ($this->_requestItems == null) {
            $qqadvproductData = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()->addFieldToFilter('quote_id', array("eq" => $this->getQuoteId()));

            // Get full products objects, with child products, for requested products
            //sum of products weight
            $final_weight = 0;

            foreach ($qqadvproductData as $qqadvproduct) {
                //weight of this product request
                $weight = 0;

                // Load Item prices
                $quoteItems = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', array("eq" => $qqadvproduct->getQuoteId()))
                    ->addFieldToFilter('request_qty', array("eq" => $qqadvproduct->getQty()))
                    ->addFieldToFilter('quoteadv_product_id', array("eq" => $qqadvproduct->getId()))
                    ->load();

                foreach ($quoteItems as $quoteItem) {
                    $product = Mage::getModel("catalog/product")->load($quoteItem->getProductId());
                    $product->setHasOptions($qqadvproduct->getHasOptions());
                    $product->setOptions($qqadvproduct->getOptions());
                    $product->setSkipCheckRequiredOption(true);

                    if($qqadvproduct->getUseDiscount() == 1){
                        $noDiscount = 0;
                    } else {
                        if($this->getSalesrule() != null){
                            $noDiscount = 0;
                        } else {
                            $noDiscount = 1;
                        }
                    }

                    $attributes = unserialize($qqadvproduct->getAttribute());

                    $item = Mage::getModel('sales/quote_item')
                        ->setQuote($this)
                        ->setProduct($product)
                        ->setQty($quoteItem->getRequestQty())
                        ->setStoreId($this->getStoreId())
                        ->setCalculationPrice($quoteItem->getOriginalCurPrice())
                        ->setCustomPrice($quoteItem->getOwnerCurPrice())
                        ->setClientRequest($qqadvproduct->getClientRequest())
                        ->setNoDiscount($noDiscount)
                        ->addOption(array(
                            'code' => 'info_buyRequest',
                            'value' => $qqadvproduct->getAttribute(),
                            'product_id' => $quoteItem->getProductId(),
                            'product' => $product
                        ));

                    //get bundle options
                    if(isset($attributes) && !empty($attributes)){
                        if(isset($attributes['bundle_option'])){
                            $item->addOption(array(
                                'code' => 'bundle_selection_ids',
                                'value' => serialize($attributes['bundle_option']),
                                'product_id' => $quoteItem->getProductId(),
                                'product' => $product
                            ));
                        }
                    }

                    //get options
                    if(isset($attributes) && !empty($attributes)){
                        if(isset($attributes['options'])){
                            $item->addOption(array(
                                'code' => 'bundle_option_ids',
                                'value' => serialize($attributes['options']),
                                'product_id' => $quoteItem->getProductId(),
                                'product' => $product
                            ));
                        }
                    }

                    //get bundle options childs
                    if(isset($attributes) && !empty($attributes)){
                        if(isset($attributes['bundle_option'])){
                            foreach($attributes['bundle_option'] as $key => $option){
                                if(!is_array($option)){
                                    $childId = Mage::getModel('bundle/selection')->load($option)->getData('product_id');
                                    $childProduct = Mage::getModel('catalog/product')->load($childId);
                                    if(isset($attributes['bundle_option_qty'][$key])){
                                        $childQty = $attributes['bundle_option_qty'][$key];
                                    }else{
                                        $childQty = 0;
                                    }

                                    $child = Mage::getModel('sales/quote_item')
                                        ->setQuote($this)
                                        ->setProduct($childProduct)
                                        ->setQty($childQty)
                                        ->setStoreId($this->getStoreId());

                                    $item->addChild($child);
                                    $weight = $weight + ($childProduct->getWeight() * $childQty);

                                } else {
                                    foreach($option as $opt){
                                        $childId = Mage::getModel('bundle/selection')->load($opt)->getData('product_id');
                                        $childProduct = Mage::getModel('catalog/product')->load($childId);
                                        $childQty = 1;

                                        $child = Mage::getModel('sales/quote_item')
                                            ->setQuote($this)
                                            ->setProduct($childProduct)
                                            ->setQty($childQty)
                                            ->setStoreId($this->getStoreId());

                                        $item->addChild($child);
                                        $weight = $weight + ($childProduct->getWeight() * $childQty);
                                    }
                                }
                            }
                        }

                        if(isset($attributes['super_attribute']) && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($attributes['super_attribute'], $product);
                            $childProduct = Mage::getModel('catalog/product')->load($childProduct->getEntityId());

                            $child = Mage::getModel('sales/quote_item')
                                ->setQuote($this)
                                ->setProduct($childProduct)
                                ->setQty('1')
                                ->setStoreId($this->getStoreId());

                            $item->addChild($child);

                            //configureble tax class fix:
                            $item->getProduct()->setTaxClassId($childProduct->getTaxClassId());

                            $weight = $weight + $childProduct->getWeight();
                        }
                    }

                    $items[] = $item;
                    $weight = $weight + $product->getWeight();
                    $weight = $weight * $quoteItem->getRequestQty();
                    $final_weight = $final_weight + $weight;
                }
            }

            if(isset($items)){
                $this->_requestItems = $items;
                // Set Total Item weight for quote
                $this->_weight = $final_weight;
            } else {
                //no request items, but array is expected
                $this->_requestItems = array();
            }

        }
        return $this->_requestItems;
    }

    /**
     * ================================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->addProductAdvanced()
     * ================================================================================
     *
     * Advanced func to add product to quote - processing mode can be specified there.
     * Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|Varien_Object $request
     * @param null|string $processMode
     * @return Mage_Sales_Model_Quote_Item|string
     */
    public function addQuoteProductAdvanced(Mage_Catalog_Model_Product $product, $request = null, $processMode = null)
    {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = new Varien_Object(array('qty' => $request));
        }
        if (!($request instanceof Varien_Object)) {
            Mage::throwException(Mage::helper('sales')->__('Invalid request for adding product to quote.'));
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);
            //C2Q customized _addCatalogQuoteProduct()
            $item = $this->_addCatalogQuoteProduct($candidate, $candidate->getCartQty());
            if ($request->getResetCount() && !$stickWithinParent && $item->getId() === $request->getId()) {
                $item->setData('qty', 0);
            }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->addQty($candidate->getCartQty());
            $item->removeErrorInfosByParams(Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        Mage::dispatchEvent('sales_quote_product_add_after', array('items' => $items));

        return $item;
    }

    /**
     * ======================================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->_addCatalogQuoteProduct()
     * ======================================================================================
     *
     * Adding catalog product object data to quote
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    protected function _addCatalogQuoteProduct(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $newItem = false;
        // C2Q - customized getQuoteItemByProduct()
        $item = $this->getQuoteItemByProduct($product);
        if (!$item) {
            $item = Mage::getModel('sales/quote_item');
            $item->setQuote($this);
            if (Mage::app()->getStore()->isAdmin()) {
                $item->setStoreId($this->getStore()->getId());
            } else {
                $item->setStoreId(Mage::app()->getStore()->getId());
            }
            $newItem = true;
        }

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())
            ->setProduct($product);

        // Add only item that is not in quote already (there can be other new or already saved item
        if ($newItem) {
            $this->addItem($item);
        }

        return $item;
    }

    /**
     * ===============================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->getItemByProduct()
     * ===============================================================================
     *
     * Retrieve quote item by product id
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item || false
     */
    public function getQuoteItemByProduct($product)
    {
        // C2Q customized getAllQuoteItems()
        foreach ($this->getAllQuoteItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }

    /**
     * ===============================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->getItemByProduct()
     * ===============================================================================
     * ADDED: excluding products from 'sales/quote_item'
     * ===============================================================================
     *
     * Retrieve quote items array
     *
     * @return array
     */
    public function getAllQuoteItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getId()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    public function checkQuoteAmount($amount)
    {
        if (!$this->getHasError() && ($amount >= self::MAXIMUM_AVAILABLE_NUMBER)) {
            $this->setHasError(true);
            $this->addMessage(
                Mage::helper('sales')->__('Items maximum quantity or price do not allow checkout.')
            );
        }
    }

    public function getCustomer()
    {
        return Mage::getModel('customer/customer')->load($this->getCustomerId());
    }

    public function getCustomerGroupId()
    {
        return $this->getCustomer()->getGroupId();
    }

    public function getItemById($id)
    {
        return Mage::getModel('qquoteadv/requestitem')->load($id);
    }

    public function getCouponCode()
    {
        return $this->getData('coupon_code');
    }

    /**
     * Retrieve Full Tax info from quote
     *
     * @return boolean
     */
    public function getFullTaxInfo()
    {
        foreach ($this->getTotals() as $total) {
            if ($total->getCode() == 'tax') {
                if ($fullInfo = $total->getData('full_info')) {
                    return $fullInfo;
                }
            }
        }
        return false;
    }

    public function getGrandTotalExclTax()
    {
        return $this->getGrandTotal() - $this->getTaxAmount();
    }

    /**
     * Get customername formatted
     *
     * @param array $address
     * @param string $prefix
     * @return string
     */
    public function getNameFormatted($address, $prefix = null)
    {
        return Mage::helper('qquoteadv')->getNameFormatted($address, $prefix = null);
    }

    /**
     * Create array from streetdata
     * in case multi line address
     *
     * ## address will be DEPRECATED ##
     *
     * @param array $address
     * @return array
     */
    public function getStreetFormatted($address)
    {
        if (isset($address['street'])) {
            return explode(',', $address['street']);
        } elseif (isset($address['address'])) { // 'address' will be DEPRECATED
            return explode(',', $address['address']);
        }
        return array();
    }

    /**
     * Format City and Zipcode
     *
     * @param array $address
     * @return string
     */
    public function getCityZipFormatted($address)
    {
        $cityZip = '';
        $city = false;
        if (isset($address['city'])) {
            $cityZip .= $address['city'];
            $city = true;
        }
        if (isset($address['postcode'])) {
            if ($city === true) {
                $cityZip .= ', ';
            }
            $cityZip .= $address['postcode'];
        }

        return $cityZip;
    }

    /**
     * Format address by type
     *
     * @param string $type
     * @return array
     */
    public function getAddressFormatted($type = null)
    {
        if ($type == null) {
            return null;
        }

        // Declare variables        
        $return = '';
        $name = '';
        $company = '';
        $street = '';
        $cityZip = '';
        $region = '';
        $country = '';
        $telephone = '';

        // Get address info
        $addressData = $this->getAddressInfoByType($type);
        // Name
        $name = $this->getNameFormatted($addressData->getData());
        // Company
        if ($addressData->getData('company')) {
            $company = $addressData->getData('company');
        }
        // Street
        $preFix = '';
        foreach ($this->getStreetFormatted($addressData->getData()) as $streetLine) {
            $street .= $preFix . $streetLine;
            $preFix = ", ";
        }
        // City and Zipcode
        $cityZip = $this->getCityZipFormatted($addressData->getData());
        //Region
        if ($addressData->getData('region')) {
            $region = $addressData->getData('region');
        } elseif ($addressData->getData('region_id')) {
            $region = Mage::getModel('directory/region')->load($addressData->getData('region_id'))->getName();
        }
        // Country
        $country = Mage::getModel('directory/country')->load($addressData->getData('country_id'))->getName();
        // Telephone
        if ($addressData->getData('telephone')) {
            $telephone = 'T: ' . $addressData->getData('telephone');
        }

        return array('name' => $name,
            'company' => $company,
            'street' => $street,
            'cityzip' => $cityZip,
            'region' => $region,
            'country' => $country,
            'telephone' => $telephone
        );
    }

    public function getWeight()
    {
        if ($this->_weight == null) {
            // reset weight
            $this->_weight = 0;
            // weight is set in getAllRequestItems()
            $this->getAllRequestItems();
        }
        return $this->_weight;
    }

    /**
     * Get Total Quote items Qty
     *
     * @return int
     */
    public function getItemsQty()
    {
        if ($this->_itemsQty == null) {
            $this->_itemsQty = 0;
            $items = $this->getAllRequestItems();
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $this->_itemsQty += $item->getData('qty');
            }
        }

        return $this->_itemsQty;
    }

    public function getIsCustomShipping()
    {
        if ($this->getShippingType() == "I" || $this->getShippingType() == "O") {
            return true;
        }
        return false;

    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getSalesRepresentative()
    {
        if (!$this->hasData('user')) {
            $user = Mage::getModel('admin/user')->load($this->getUserId());
            $this->setData('user', $user);
        }
        return $this->getData('user');
    }

    /**
     * Get sender info for quote
     *
     * @return array
     */
    public function getEmailSenderInfo()
    {
        // Sender from store
        $senderValue = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/sender', $this->getStoreId());
        if (empty($senderValue)) {
            // Default setting
            $senderValue = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/sender', 0);
            // fallback
            if (empty($senderValue)) {
                $admin = Mage::getModel("admin/user")->getCollection()->getData();
                return array(
                    'name' => $admin[0]['firstname'] . " " . $admin[0]['lastname'],
                    'email' => $admin[0]['email'],
                );
            }
        }

        if ($senderValue == 'qquoteadv_sales_representive') {
            return array(
                'name' => $this->getSalesRepresentative()->getName(),
                'email' => $this->getSalesRepresentative()->getEmail()
            );
        }

        $email = Mage::getStoreConfig('trans_email/ident_' . $senderValue . '/email', $this->getStoreId());
        if (!empty($email)) {
            return array(
                'name' => Mage::getStoreConfig('trans_email/ident_' . $senderValue . '/name', $this->getStoreId()),
                'email' => $email
            );
        }

        return array(
            'name' => $senderValue,
            'email' => $senderValue
        );
    }

    /**
     * Get list of available coupons
     *
     * @param   int || array        // $customerGroup
     * @return  array               // array with available coupons
     */
    public function getCouponList($websiteId, $customerGroup)
    {
        $couponCollection = Mage::getModel('salesRule/rule')->getCollection();
        $couponCollection->addWebsiteGroupDateFilter(1, $customerGroup, Mage::getModel('core/date')->date('Y-m-d'));

        if ($couponCollection){
            $couponList = null;
            foreach ($couponCollection as $coupon) {
                if ($coupon->getData('code') != null) {
                    $couponList[] = $coupon->getData();
                }
            }
            return $couponList;
        }

        return false;
    }

    /**
     * Create options array from coupon list
     *
     * @param   int || array        // $customerGroup
     * @return  array               // array with available coupons
     */
    public function getCouponOptions($websiteId, $customerGroup)
    {
        $couponList = $this->getCouponList($websiteId, $customerGroup);

        if ($couponList) {
            $return[0] = Mage::helper('qquoteadv')->__('-- Select Coupon --');
            foreach ($couponList as $coupon) {
                $return[$coupon['rule_id']] = $coupon['name'];
            }
            return $return;
        }

        return false;
    }

    /**
     * Retrieve Coupon name from id
     *
     * @param   int // $couponId
     * @return  string
     */
    public function getCouponNameById($couponId)
    {
        $couponCollection = Mage::getModel('salesRule/rule')->load($couponId, 'rule_id');
        return $couponCollection->getData('name');
    }

    /**
     * Retrieve Coupon code from id
     *
     * @param   int // $couponId
     * @return  string
     */
    public function getCouponCodeById($couponId)
    {
        if ($couponCollection = Mage::getModel('salesRule/rule')->load($couponId, 'rule_id')) {
            return $couponCollection->getData('coupon_code');
        } else {
            return false;
        }
    }

    public function setShippingMethod(Ophirah_Qquoteadv_Model_Quoteshippingrate $rateData)
    {
        if ($rateData) {
            $address = $this->getAddress();
            $address->setData('shipping_method', $rateData->getData('code'));
            $address->setData('base_shipping_amount', (float)$rateData->getData('price'));
//            $address->setData('shipping_amount', $rateData->getData('price') * $rate);
            $address->setData('collect_shipping_rates', true);
            $address->save();

            $this->setData('shipping_method', $address->getData('shipping_method'));
            $this->setData('base_shipping_amount', $address->getData('base_shipping_amount'));
            $this->setData('shipping_amount', $address->getData('shipping_amount'));
        }
    }

    /**
     * Remove Shipping Method from Quote
     *
     */
    public function unsetShippingMethod()
    {
        // Data to reset
        $resetArray = array('shipping_type',
            'shipping_price',
            'shipping_code',
            'shipping_carrier',
            'shipping_carrier_title',
            'shipping_method',
            'shipping_method_title',
            'shipping_amount',
            'shipping_description',
            'base_shipping_amount',
            'address_shipping_description',
            'address_shipping_method'
        );

        try {
            foreach ($resetArray as $reset) {
                $this->setData($reset, null);
                $this->getAddress()->setData($reset, null);
//                $this->getAddress()->save();
//                $this->save();
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }
    }

    /**
     * Get Proposal sent date
     * if proposal is not sent yet,
     * use created at date
     *
     * @return string
     */
    public function getProposalDate()
    {
        if ($this->isValidDate($this->getData('proposal_sent'))) {
            return $this->getData('proposal_sent');
        } else {
            return $this->getData('created_at');
        }
    }

    /**
     * Check if database date has a value
     *
     * @param string $date
     * @return bool
     */
    public function isValidDate($date)
    {

        $return = false;

        if (!is_string($date)) {
            $message = 'Date is not a string';
            Mage::log('Message: ' .$message, null, 'c2q.log', true);
            return false;
        }

        if ($intDate = (int)$date) {
            $return = true;
        }

        return $return;
    }

    /**
     * Check if data is changed in the quote that could require a new quote
     *
     * @param $data
     * @return bool
     */
    public function isImportantDataChanged($data){
        //this function could be used by customers to make versioning conditional
        return true;
    }

    /**
     * This function is needed to support Fooman surcharge
     */
    public function getPayment(){
        $salesQuoteId = $this->getCreatedFromQuoteId();

        if(isset($salesQuoteId) && $salesQuoteId != null){
            $salesQuote = Mage::getModel("sales/quote")->loadByIdWithoutStore($salesQuoteId);
            return $salesQuote->getPayment();
        }

        $payment = Mage::getModel('sales/quote_payment');
        $this->addPayment($payment);
        return $payment;
    }

    /**
     * Generate a random password
     *
     * @param int $length
     * @return string           // Random password
     */
    protected function _generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    public function setStatus($status){
        $statusArray = Mage::getModel('qquoteadv/status')->getOptionArray(true);
        $message = 'Status changed to <strong>'.$statusArray[$status].'</strong>';

        if((intval($status) >= 50) && (intval($status) < 60)){
            //proposal state so trail the prices that are send to the customer
            $items = $this->getAllRequestItems();
            $itemsMessage = '';
            foreach ($items as $item){
                $itemsMessage .= $item->getQty().'x '.$item->getName().' for '.$item->getCustomPrice().'<br />';
            }
            $message = $itemsMessage . '<br />' . $message;
        }

        Mage::getModel('qquoteadv/quotetrail')->addMessage($message, $this->getQuoteId());
        $this->setData('status', $status);
    }

    /**
     * Allows you to upload a file to media/qquoteadv/[quote id]/[file name]
     * You need to specify the file name and the form key of the FILE type.
     * @param $fileTitle
     * @param $formDataName
     */
    public function uploadFile($fileTitle, $formDataName)
    {
        if (array_key_exists($formDataName, $_FILES)) {
            if ($_FILES[$formDataName]['error'] == 0) {
                if (empty($fileTitle)) {
                    if (isset($_FILES[$formDataName]['name'])) {
                        $fileTitle = $_FILES[$formDataName]['name'];
                    } else {
                        $fileTitle = 'File_' . $this->getData('increment_id');
                    }
                }

                $extensionCheck = pathinfo($fileTitle);
                if(empty($extensionCheck['extension'])){
                    $extension = pathinfo($_FILES[$formDataName]['name']);
                    $fileTitle = $fileTitle.'.'.$extension['extension'];
                }

                $path = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($this->getId());

                $uploader = new Varien_File_Uploader($formDataName);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);

                try {
                    $uploader->save($path, $fileTitle);
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

            } else {
                if (is_integer($_FILES[$formDataName]['error'])) {
                    Mage::helper('qquoteadv/file')->getPhpFileErrorMessage($_FILES[$formDataName]['error']);
                } else {
                    $message = "An non-integer is given as error when uploading a file to a quote.";
                    Mage::log('Message: ' .$message, null, 'c2q.log', true);
                }
            }
        }
    }

    /**
     * Removes a single file from the quote
     * @param $fileTitle
     * @return boolean
     */
    public function removeFile($fileTitle){
        $pathToFile = 'media/qquoteadv/'.$this->getId().'/'.$fileTitle;
        $fileRemoved = false;
        try {
            unlink($pathToFile);
            $fileRemoved = true;
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $fileRemoved;
    }

    /**
     * Same function as getFileUploadsToHtml but adds additional CSS for static transactional email template.
     * @return string
     */
    public function getFileUploadsToHtmlStatic(){
        $style = 'font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;';
        return $this->getFileUploadsToHtml($style);
    }

    /**
     * Function made for the transactional emails. Returns table rows of available uploads on the quote.
     * @param string $style
     * @return string
     */
    public function getFileUploadsToHtml($style = ''){
        $pathOfDir = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($this->getId());
        $html = "";
        if(file_exists($pathOfDir)) {
            if ($handle = opendir($pathOfDir)) {
                while (false !== ($entry = readdir($handle))) {
                    $file_parts = pathinfo($entry);
                    if (!empty($file_parts['extension'])) {
                        $pathOfFile = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadPath(array('dir' => $this->getId(), 'file' => $entry));
                        $html .= '<tr>
                                    <td class="address-details" style="'.$style.'"><a href="'.$pathOfFile. '">' . $file_parts['filename'] . '</a></td>
                                  </tr>';
                    }
                }
                closedir($handle);
            }
        }
        return $html;
    }

}
