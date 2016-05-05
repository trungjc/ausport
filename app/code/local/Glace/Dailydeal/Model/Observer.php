<?php

class Glace_Dailydeal_Model_Observer
{

    /**
     * Set special price for product
     */
    protected function _SetProductPriceAndWatermark($model_product, $needsWatermark)
    {
        $dailydeal_collection = Mage::getModel('dailydeal/dailydeal')->getCollection();
        $model_deal = $dailydeal_collection->loadcurrentdeal($model_product->getId());
        if ($model_deal) {
            $flag_qty = $model_deal->checkDealQty($model_product, $model_deal);
            $flag_price = $model_deal->checkDealPrice($model_product);
            if ($flag_qty && $flag_price) {
                $model_product->setSpecialPrice($model_product->getPrice());
                $model_product->setFinalPrice($model_deal->getDailydealPrice());
            }
        }
    }

    /**
     * Set price for view product
     * Listen event catalog_product_get_final_price
     * @param Varien_Event_Observer $observer
     */
    public function catalog_product_get_final_price($observer)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $product = $observer->getProduct();
        $deal = Glace_Dailydeal_Model_Dailydeal::getModel();
        $deal->loadByProductId($product->getId());
        if ($deal->getId()) {
            $this->_SetProductPriceAndWatermark($product, true);
        }
    }

    /**
     * Set price for list product in category
     * Listen event catalog_product_collection_load_after
     * @param Varien_Event_Observer $observer
     */
    public function catalogproduct_collectionload_after($observer)
    {
        if (!Mage::helper('dailydeal/toolasiaconnect')->isModuleOutputEnabled()) {
            return;
        }

        $deal = Glace_Dailydeal_Model_Dailydeal::getModel();
        foreach ($observer->getCollection()->getItems() as $product) {
            $deal->setData(array());
            $deal->loadByProductId($product->getId());

            if ($deal->getId()) {
                $this->_SetProductPriceAndWatermark($product, true);
            }
        }
    }

    /**
     * Check quantity follow Deal when add to cart
     * Listen event sales_quote_item_qty_set_after
     * @param Varien_Event_Observer $observer
     */
    public function checkQuoteItemQty($observer)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $quoteItem = $observer->getData('item');
        $qty = $quoteItem->getData('qty');

        $result = new Varien_Object();
        $result->setData('has_error', false);

        $deal = Mage::getModel('dailydeal/dailydeal')->getCollection()->loadcurrentdeal($quoteItem->getData('product_id'));

        if ($deal != null) {
            // Check deal's qty __111
            $currentQty = $deal->getData('deal_qty') - $deal->getData('sold_qty');

            $product_id = $quoteItem->getData('product_id');
            $model_product = Mage::getModel('catalog/product')->load($product_id);

            if (!$deal->checkSoldQty($model_product, $deal, $qty)) {
                $message = Mage::helper('cataloginventory')->__("The requested quantity for '%s' not available in Deal. Deal quantity left: %s", $deal->getData('cur_product'), $currentQty);
                $result->setData('has_error', true)
                        ->setData('message', $message)
                        ->setData('quote_message', $message)
                        ->setData('quote_message_index', 'qty');
            }

            // Check Limit deals pear customer __222
            $totallimit = $deal->getData('limit_customer');
            if ($totallimit > 0) {
                if ($qty > $totallimit) {
                    $message = Mage::helper('cataloginventory')->__("Quantity you chose exceed the deal quantity (%s) that you are allowed to buy!", $totallimit);
                    $result->setData('has_error', true)
                            ->setData('message', $message)
                            ->setData('quote_message', $message)
                            ->setData('quote_message_index', 'qty');
                }
            }
            
            if (version_compare(Mage::getVersion(), '1.6.0', '>='))
            {
                  // app\code\core\Mage\CatalogInventory\Model\Observer.php line 320 trong magento 1.7
                  if ($result->getHasError()) {
                    $quoteItem->addErrorInfo(
                            'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, $result->getData('message')
                    );
                    $quoteItem->getQuote()->addErrorInfo(
                            $result->getQuoteMessageIndex(), 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, $result->getData('quote_message')
                    );
                }
            }else{
                if ($result->getHasError()) {
                    $quoteItem->setHasError(true)
                            ->setMessage($result->getMessage());
                    $quoteItem->getQuote()->setHasError(true)
                        ->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
                }
            }
        }
        return $this;
    }

    /**
     * Listen event checkout_cart_update_items_after
     * Update lai quantity trong shopping cart, xem co vuot qua so luong deal con lai ko?
     * type product: simple, configurable
     * @param Varien_Event_Observer $observer
     * 
     * This function only set error in session. And Program allows update quantity to quote item.
     */
    public function checkoutcart_updateitems_after($observer)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $cart = $observer->getData('cart');
        $info = $observer->getData('info');

        foreach ($info as $dataId => $dataInfo) {
            $item = $cart->getData('quote')->getItemById($dataId);
            $deal = Mage::getModel('dailydeal/dailydeal')->getCollection()->loadcurrentdeal($item->getProductId());

            if ($deal) {

                // Check deal's qty __333
                $model_product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
                $flag_sold_qty = $deal->checkSoldQty($model_product, $deal, $dataInfo['qty']);

                if (!$flag_sold_qty) {
                    $dealqty = $deal->getDealQty();
                    $soldqty = $deal->getSoldQty();
                    $qty_left = $dealqty - $soldqty;

                    $this->_getSession()->addError("The quantity that you have inserted is over deal quantity left ($qty_left). Please reinsert another one!");
                }
            }
        }
        return $this;
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Listen event sales_order_place_after
     * @param Varien_Event_Observer $observer
     */
    public function salesorder_placeafter($observer)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();
        $order_id = $order->getData('entity_id');
        foreach ($items as $item) {

            $productid = $item->getProductId();

            if ($productid) {
                $deal = Glace_Dailydeal_Model_Dailydeal::getModel()->loadByProductId($productid);

                if ($deal->getId()) {

                    $sold_qty = $item->getData('qty_ordered') + $deal->getData('sold_qty');
                    $deal   ->setSoldQty($sold_qty)
                            ->insertOrderId($order_id)->save();
                    
                    // Action : disable product after place order success, update deal success
                    Glace_Dailydeal_Model_Product::getInstance()->disableProductByDealId(array($deal->getId()));

                    $info_buyRequest = Glace_Dailydeal_Model_Order::getInstance()->markProductOfDeal($item, $deal);
                    $item->setProductOptions($info_buyRequest);
                }
            }
        }

        return $this;
    }

    public function runCronInFiveMinute()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        // disable Product if deal expire time
        Glace_Dailydeal_Model_Business::autoDisableProduct();
    }

    public function runCronAtTwentyThree()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        // Generate deal
        Glace_Dailydeal_Model_Business::autoGenerateDeal();

        // Check system will have deal running tomorrow
        Glace_Dailydeal_Model_Business::autoSendMail();
    }

    /**
     * Backend : when admin update order's status
     * Listen event sales_order_save_after
     * @param Varien_Event_Observer $observer
     */
    public function orderSaveAfter($observer)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $model_order = Glace_Dailydeal_Model_Order::getInstance();

        $order = $observer->getData('order');

        if ($order->getData('status') == Mage_Sales_Model_Order::STATE_CANCELED) {
            $model_order->cancelOrderUpdateDealQuantity($observer);
            $model_order->cancelOrderEnableProduct($observer);
        }
        if ($order->getData('status') == Mage_Sales_Model_Order::STATE_CLOSED) {
            $model_order->cancelOrderUpdateDealQuantity($observer);
            $model_order->cancelOrderEnableProduct($observer);
        }
    }

    /**
     * Listen event controller_action_layout_generate_blocks_before
     */
    public function controller_action_layout_generate_blocks_before($observer)
    {
        Glace_Dailydeal_Model_Layout::overrideProductView($observer);
    }
	/*
    public function checkLicense($o)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules; 
        $modules2 = array_keys((array)Mage::getConfig()->getNode('modules')->children()); 
        if(!in_array('Glace_Mcore', $modules2) || !$modulesArray['Glace_Mcore']->is('active') || Mage::getStoreConfig('mcore/config/enabled')!=1)
        {
            Mage::helper('dailydeal')->disableConfig();
        }
	}
	*/
}