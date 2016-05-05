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

class Ophirah_Qquoteadv_Model_Observer
{
    /**
     * Change status to Request expired
     */
    public function updateStatusRequest()
    {
        $now = Mage::getSingleton('core/date')->gmtDate();
        $items = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
        $items->addFieldToFilter('status', Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST);
        $items->getSelect()->group('store_id');
        if ($items->getSize() > 0) {
            $data = $items->getData();

            foreach ($data as $unit) {
                $storeId = $unit['store_id'];
                $day = Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal', (int)$storeId);

                $now = Mage::getSingleton('core/date')->gmtDate();
                $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
                $collection->addFieldToFilter('status', Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST);
                $collection->getSelect()
                    ->where('created_at<INTERVAL -' . $day . ' DAY + \'' . $now . '\'');
                $collection->load();

                foreach ($collection as $item) {
                    $item->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_EXPIRED);
                    $item->save();
                }
            }
        }
    }

    /**
     * Change status to Proposal expired
     */
    public function updateStatusProposal()
    {
        $now = Mage::getSingleton('core/date')->gmtDate("Y-m-d");
        $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
        $quote_status = Mage::getModel('qquoteadv/status')->statusExpire();
        $collection->addFieldToFilter('status', array('in' => $quote_status));
        $collection->getSelect()->where('expiry < \'' . $now . '\' AND no_expiry = \'0\'');
        $collection->load();

        foreach ($collection as $item) {
            $item->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED);
            $item->save();
        }
    }

    /**
     * Switch between default layout and c2q module layout
     */
    public function switchQuoteLayout($observer)
    {
        $updatesRoot = $observer->getUpdates();
        $moduleName = 'qquoteadv';
        $enabled = Mage::getStoreConfig('qquoteadv_general/quotations/enabled', Mage::app()->getStore()->getStoreId());
        if ($enabled && !Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl') && !Mage::app()->getStore()->isAdmin()) {
            foreach ($updatesRoot->children() as $updateNode) {
                if ($moduleName == $updateNode->getName()) {
                    $dom = dom_import_simplexml($updateNode);
                    $dom->parentNode->removeChild($dom);
                }
            }
        }
        return $this;
    }

    public function setCustomPrice($observer)
    {
        $customPrice = Mage::registry('customPrice');
        if (!isset($customPrice)) {
            return $this;
        }

        if (!Mage::helper('customer/data')->isLoggedIn() && !Mage::getSingleton('admin/session')->isLoggedIn()) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote_Item $quote_item */
        $quote_item = $observer->getQuoteItem()->getParentItem();
        if (!$quote_item) {
            $quote_item = $observer->getQuoteItem();
        }

        $quote_item->setCustomPrice($customPrice)->setOriginalCustomPrice($customPrice);


        Mage::unregister('customPrice');
        return $this;
    }

    public function setAdminCustomPrice($observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $customPrice = Mage::registry('customPrice');
            if (isset($customPrice)) {

                $event = $observer->getEvent();
                $quote_item = $event->getQuoteItem();

                $quote_item->setCustomPrice($customPrice)->setOriginalCustomPrice($customPrice);

                try {
                    $quote_item->save();
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                }

                Mage::unregister('customPrice');
            }
        }

        // set session data
        Mage::getSingleton('adminhtml/session_quote')->setData('update_quote_key', 'from_quote');

        return $this;
    }

    public function disableRemoveQuoteItem(Varien_Event_Observer $observer)
    {
        if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {
            $product = $observer->getQuoteItem();
            $product->isDeleted(false);

            $message = Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);
        }
        return $this;
    }

    //#log out from quote confirmation mode
    public function logoutFromQuoteConfirmationMode(Varien_Event_Observer $observer)
    {
        if (Mage::helper('qquoteadv')->isActiveConfirmMode(true)) {
            Mage::helper('qquoteadv')->setActiveConfirmMode(false);
        }
    }

    public function disableQtyUpdate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {
            $cartData = Mage::app()->getRequest()->getParam('cart');
            foreach ($cartData as $index => $data) {
                if (isset($data['qty'])) {
                    $cartData[$index]['qty'] = null;
                }
            }
            Mage::app()->getRequest()->setParam('cart', $cartData);

            $link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
            $message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.", $link);
            Mage::getSingleton('checkout/session')->addNotice($message);
        }

    }

    public function disableUpdateItemOptions(Varien_Event_Observer $observer)
    {
        if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {

            Mage::app()->getRequest()->setParam('id', null);

            $message = Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);

            $link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
            $message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.", $link);
            Mage::getSingleton('checkout/session')->addNotice($message);
        }
    }

    public function disableAddProduct(Varien_Event_Observer $observer)
    {
        if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {

            Mage::app()->getRequest()->setParam('product', '');

            $message = Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
            Mage::getSingleton('checkout/session')->addError($message);

            $link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
            $message = Mage::helper('qquoteadv')->__("To update item in the Shopping cart <a href='%s'>log out</a> from Quote confirmation mode.", $link);
            Mage::getSingleton('checkout/session')->addNotice($message);
        }
    }

    public function addC2qRefNumber(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        
        $c2qIdAssignedToMageIds = Mage::getSingleton('core/session')->getData('c2qIdAssignedToMageIds');
        if(is_null($c2qIdAssignedToMageIds)){
            $c2qIdAssignedToMageIds = array();
        }
        
        foreach($c2qIdAssignedToMageIds as $key => $assignment){
            if(is_array($assignment)){
                foreach($assignment as $mageId => $c2qId){
                    if($mageId == $quote->getData('entity_id')){
                        $order->setData('c2q_internal_quote_id', $c2qId);
                        unset($c2qIdAssignedToMageIds[$key]);
                        Mage::getSingleton('core/session')->setData('c2qIdAssignedToMageIds', $c2qIdAssignedToMageIds);
                        return;
                    }
                }
            }
        }
    }

    public function setQuoteStatus($event)
    {
        $quoteId = Mage::getSingleton('core/session')->proposal_quote_id;
        if (empty($quoteId)) {
            $quoteId = Mage::getSingleton('adminhtml/session')->getUpdateQuoteId();
        }

        if ($_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId)) {
            $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_ORDERED);

            try {
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
                $_quoteadv->save();
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));

                if (Mage::getSingleton('core/session')->proposal_quote_id) {
                    Mage::getSingleton('core/session')->proposal_quote_id = null;
                }
                if (Mage::getSingleton('adminhtml/session')->getUpdateQuoteId()) {
                    Mage::getSingleton('adminhtml/session')->setUpdateQuoteId(null);
                }
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
    }

    public function quoteCancelation($observer)
    {

        $event = $observer->getEvent();
        $product = $event->getProduct();

        if ($product && $product->getId()) {

            $table = Mage::getSingleton('core/resource')->getTableName('qquoteadv/qqadvcustomer');

            $_collection = Mage::getModel("qquoteadv/qqadvproduct")->getCollection();
            $_collection->getSelect()->join(array('p' => $table), 'main_table.quote_id=p.quote_id', array());
            $_collection->addFieldToFilter("status", array("neq" => Ophirah_Qquoteadv_Model_Status::STATUS_CANCELED));

            $productId = $product->getId();
            $quoteIds = array();
            foreach ($_collection as $item) {
                if ($productId == $item->getData('product_id')) {
                    $quoteIds[] = $item->getData('quote_id');
                }
            }

            foreach ($quoteIds as $quoteId) {
                $quote = Mage::getModel("qquoteadv/qqadvcustomer")->load($quoteId);
                $quote->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_CANCELED);
                try {
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $quote));
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforecancel', array('quote' => $quote));
                    $quote->save();
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $quote));
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftercancel', array('quote' => $quote));
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                }
            }
        }

    }

    public function blockClassListener($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ("Mage_Adminhtml_Block_Sales_Order_Create_Totals" === get_class($block)) {
            $block->setTemplate("qquoteadv/sales/order/create/totals.phtml");
        }
        return $this;
    }

    public function setAllowedToQuoteMode($observer)
    {
        if (!Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() != 'adminhtml') {
            $product = $observer->getEvent()->getProduct();
            $allowed = Mage::helper('qquoteadv/licensechecks')->getAllowedToQuoteMode($product);
            $product->setAllowedToQuotemode($allowed);
        }
        return $this;
    }

    public function addAdminGroupAllow($observer)
    {
        $form = $observer->getEvent()->getForm();
        $groupAllow = $form->getElement('group_allow_quotemode');
        if ($groupAllow) {
            $groupAllow->setRenderer(
                Mage::getSingleton('core/layout')->createBlock('qquoteadv/adminhtml_catalog_product_edit_tab_qquoteadv_group_allow')
            );
        }
    }

    public function addCostTierPrice($observer){
        $form = $observer->getEvent()->getForm();
        $costTierPrice = $form->getElement('cost_tier_price');
        if ($costTierPrice) {
            $costTierPrice->setRenderer(
                Mage::getSingleton('core/layout')->createBlock('qquoteadv/adminhtml_catalog_product_edit_tab_qquoteadv_tiercost')
            );
        }
    }

    public function checkQuoteItemQty($observer)
    {

        if (Mage::app()->getRequest()->getModuleName() != "Ophirah_Qquoteadv" && !Mage::registry('QtyObserver')) {
            Mage::getModel('cataloginventory/observer')->checkQuoteItemQty($observer);
        } else {

        }

        return $this;

    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function salesQuoteCollectTotalsBefore($observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getData('quote');

        $this->setBundleCustomPrices($quote->getAllVisibleItems());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function ophirahQquoteadvAddressCollectTotalsBefore($observer)
    {
        $rate = null;
        $quoteId = null;
        /** @var Ophirah_Qquoteadv_Model_Address $quoteAddress */
        $quoteAddress = $observer->getData('quoteadv_address');

        if ($quoteAddress) {
            $rate = $quoteAddress->getQuote()->getData('base_to_quote_rate');
            $quoteId = $quoteAddress->getQuote()->getQuoteId();
        }

        // Check for Salesrule
        if ($salesrule = $quoteAddress->getQuote()->getData('salesrule')) {
            if ($couponCode = Mage::getModel('qquoteadv/qqadvcustomer')->getCouponCodeById($salesrule)) {
                $quoteAddress->getQuote()->setData('coupon_code', $couponCode);
            }
        }

        /** @var Mage_Sales_Model_Quote_Item $item */
        $quoteItems = array();
        foreach ($quoteAddress->getAllVisibleItems() as $key => $item) {
            $newId = $item->getQuoteId() . '00' . ($key + 1);
            $item->setId($newId);
            $quoteItems[$item->getId()] = $item;
        }
        foreach ($quoteItems as $item) {
            if (!$item->getChildren()) continue;
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($item->getChildren() as $child) {
                if ($child->getParentItemId() == null) {
                    $child->setParentItem($item);
                    $child->setParentItemId($item->getId());
                }
            }
        }

        $this->setBundleCustomPrices($quoteAddress->getAllVisibleItems(), $rate, $quoteId, $quoteAddress);
    }

    /**
     * @param Mage_Sales_Model_Quote_Item[] $quoteItems
     */
    protected function setBundleCustomPrices(array $quoteItems, $rate = null, $quoteId = null, $quoteAddress = null)
    {
        foreach ($quoteItems as $item) {
            if ($item->getProductType() != 'bundle') {
                continue;
            }

            $customPrice = $item->getCustomPrice();
            if ($customPrice === null) {
                continue;
            }

            if (!$rate > 0) {
                $rate = 1;
            }
            $customPrice = $customPrice / $rate;

            $product = $item->getProduct();

            // Reset Original Bundle Price
            $prodFinalPrice = 0;
            if (!$item->getData('quote_org_price')) {
                // Assign Original Price once
                $prodFinalPrice = $product->getFinalPrice($item->getData('qty'));
            }

            // Check tier pricing
            try {
                // For tier Qty get Custom Price
                if ($item->getData('qty') != $product->getData('qty')) {
                    $productPrice = Mage::getModel('qquoteadv/requestitem')->getCollection()
                        ->addFieldToFilter('quote_id', array('eq' => $quoteId))
                        ->addFieldToFilter('product_id', array('eq' => $item->getProductId()))
                        //->addFieldToFilter('request_qty', array('eq' => $product->getData('qty')));
                        ->addFieldToFilter('request_qty', array('eq' => $item->getData('qty')));

                    if ($productPrice) {
                        foreach ($productPrice as $prodPrice) {
                            if ($prodPrice->getData('owner_base_price') != null) {
                                $customPrice = $prodPrice->getData('owner_base_price');
                                // Storing Original Price
                                $prodFinalPrice = $prodPrice->getData('original_price');
                            }
                        }
                    }
                }

            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                $message = 'Could not get qty information for the bundle product';
                Mage::log('Message: ' .$message, null, 'c2q.log', true);
            }


            $product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_PARENT);
            $product->setData('custom_price', $customPrice);

            if ($item->getData('qty') > 0) {
                if ($prodFinalPrice > 0) {
                    // Add original price data to item
                    $item->setData('quote_org_price', $prodFinalPrice);
                }
            }

            if ($product->getTaxClassId() === null || $product->getTaxClassId() == 0) {
                $taxClass = null;
                /** @var $child Mage_Sales_Model_Quote_Item */
                foreach ($item->getChildren() as $child) {
                    if ($taxClass == null) {
                        $taxClass = $child->getProduct()->getTaxClassId();
                    } else if ($taxClass != $child->getProduct()->getTaxClassId()) {
                        $message = 'Could not determine bundle product tax class since the products within have different classes.';
                        Mage::log('Message: ' .$message, null, 'c2q.log', true);
                    }
                }
                $product->setTaxClassId($taxClass);
            }
        }
    }

    /**
     * Log email data when sending an email from Cart2Quote
     *
     * @param $observer
     */
    public function ophirahQquoteadvSendEmailBefore($observer){
        $mailTemplate = $observer->getEvent()->getData();
        if(is_array($mailTemplate)){
            foreach($mailTemplate as $template){
                if(is_object($template) && (get_class($template) == "Mage_Core_Model_Email_Template")){
                    $mailTemplate = $template;
                    break;
                }
            }
        }

        if(!is_array($mailTemplate)){
            $logEnabled = (int) Mage::getStoreConfig('qquoteadv_advanced_settings/general/force_log', $mailTemplate->getTemplateFilter()->getStoreId());

            if($logEnabled > 0){
                $log['emailData'] = $mailTemplate->getData();
                $log['emailBcc'] = $mailTemplate->getMail()->getRecipients();
                $log['emailHeader'] = $mailTemplate->getMail()->getHeaders();

                Mage::log($log, null, 'c2q_email.log', true);
            }
        }
    }

    /**
     * Register admin session key in DB like magento does for frontend users
     */
    public function logAdminSession(){
        //get current session and its id
        $currentSession = Mage::getSingleton('admin/session');
        $currentSessionId = $currentSession->getSessionId();

        //check if the user is logged in in the backend
        if($currentSession->getUser()){
            //get the user id
            $adminId = $currentSession->getUser()->getUserId();

            //load the log object of this user id
            $logAdmin = Mage::getModel('qquoteadv/qqadvlogadmin')->load($adminId, 'admin_id');

            //check if it already exists
            if ($logAdmin->getData("id")) {
                // update
                $data = array('admin_id' => $adminId,'session_id' => $currentSessionId);
                $logAdmin->addData($data);
            } else {
                // add
                $logAdmin->setData('admin_id', $adminId);
                $logAdmin->setData('session_id', $currentSessionId);
            }
            //save the object
            $logAdmin->save();

        }
    }

    /**
     * This function set the default 'allowed_to_quotemode' attribute value
     * It listens to admin_system_config_changed_section_qquoteadv observer
     */
    public function defaultCart2quoteAttribute(){
        $defaultCart2quoteAttributeValue = (int) Mage::getStoreConfig('qquoteadv_advanced_settings/general/default_cart2quote_attribute_value');

        $defaultAllowedToQuotemode = (int) Mage::getResourceModel('eav/entity_attribute_collection')
            ->setCodeFilter('allowed_to_quotemode')
            ->getFirstItem()
            ->getDefaultValue();

        if($defaultCart2quoteAttributeValue !== $defaultAllowedToQuotemode){
            if($defaultCart2quoteAttributeValue == 0){
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $setup->updateAttribute('catalog_product', 'allowed_to_quotemode', array(
                    'default_value' => '0',
                ));
            }

            if($defaultCart2quoteAttributeValue == 1){
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $setup->updateAttribute('catalog_product', 'allowed_to_quotemode', array(
                    'default_value' => '1',
                ));
            }
        }
    }

    /**
     * This code writes the Cart2Quote edition to a store config setting, so that it is readable.
     */
    public function saveCart2QuoteEdition(){
        $edition = Mage::helper('qquoteadv/license')->getEdition();
        Mage::getModel('core/config')->saveConfig('qquoteadv_general/quotations/edition', $edition);
    }

    /**
     * This code writes the Cart2Quote expiry date to a store config setting, so that it is readable.
     */
    public function saveCart2QuoteExpiryDate(){
        $expiryDate = Mage::helper('qquoteadv/license')->getC2QExpiryDate();
        Mage::getModel('core/config')->saveConfig('qquoteadv_general/quotations/expiry_date', $expiryDate);
    }

    /**
     * This code writes the Cart2Quote expiry date to a store config setting, so that it is readable.
     */
    public function saveCart2QuoteTrialExpired(){
        $hasExpired = Mage::helper('qquoteadv/license')->hasExpired();
        Mage::getModel('core/config')->saveConfig('qquoteadv_general/quotations/has_expired', $hasExpired);
    }

    /**
     * This function registers a function that needs to be called on shutdown.
     */
    public function catchFatalError(){
        register_shutdown_function(array($this, 'callRegisteredShutdown'));
    }

    /**
     * This function is called on shutdown, it checks if there has been an error in this request.
     * If so, it checks if the error is about ionCube, in that case it also checks if Cart2Quote is involved.
     *
     * If al that is true, it shows some help full information.
     */
    public function callRegisteredShutdown(){
        $error = error_get_last(); //needs PHP 5.2
        if( $error !== NULL){

            //check for ioncube wrong version
            if (strpos($error['message'], 'cannot be decoded') !== false) {

                //check for issue with Cart2Quote
                if(strpos($error['message'], 'Qquoteadv') !== false) {
                    $layout = Mage::app()->getLayout();
                    $layout->getUpdate()->addHandle('adminhtml')->load();

                    echo $layout->getBlockSingleton('qquoteadv/adminhtml_system_config_field_support')->renderWrongIc();
                }
            }
        }
    }

    /**
     * This function connects the Cart2Quote salesrep to the IWD salesrep
     *
     * @param $observer
     */
    public function setIwdSalesRep($observer)
    {
        // IWD Customization:
        if (Mage::helper('core')->isModuleEnabled('IWD_SalesRepresentative')){
            $order = $observer->getEvent()->getOrder();;
            $c2qId = $order->getData('c2q_internal_quote_id');

            if (is_numeric($c2qId)) {
                $orderId = $order->getId();
                $userId = Mage::getModel('qquoteadv/qqadvcustomer')->load($c2qId)->getData('user_id');

                //get salesrep object with this order_id
                $sales = Mage::getModel('salesrep/sales')->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->getFirstItem();
                $sales->setData('order_id', $orderId);
                $sales->setData('user_id', $userId);

                //save the new user_id
                try {
                    $sales->save();
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                }
            }
        }
    }

    public function addDownloadableFrontend($observer){
        $qquoteadvProductId = $observer->getEvent()->getQquoteadvProductId();
        $params = $observer->getEvent()->getParams();
        if(is_array($params) && array_key_exists('links', $params) && $qquoteadvProductId){
            Mage::getModel('qquoteadv/qqadvproductdownloadable')->setLinksForProduct($qquoteadvProductId, $params['links']);
        }
    }

    public function addDownloadableBackend($observer){
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if(Mage::getModel('qquoteadv/qqadvproductdownloadable')->isDownloadable($quoteItem)){
            $qquoteadvProduct = $observer->getEvent()->getQqadvproduct();
            if($qquoteadvProduct instanceof Ophirah_Qquoteadv_Model_Qqadvproduct && $qquoteadvProduct->getId()){
                $links = Mage::helper('downloadable/catalog_product_configuration')->getLinks($quoteItem);
                Mage::getModel('qquoteadv/qqadvproductdownloadable')->setLinksForProduct($qquoteadvProduct->getId(), $links);
            }
        }
    }
}
