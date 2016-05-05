<?php

class Glace_Dailydeal_Model_Dailydeal extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('dailydeal/dailydeal');
    }

    /**
     * @return Glace_Dailydeal_Model_Dailydeal
     */
    public static function getInstance()
    {
        return Mage::getSingleton('dailydeal/dailydeal');
    }

    /**
     * @return Glace_Dailydeal_Model_Dailydeal
     */
    public static function getModel()
    {
        return Mage::getModel('dailydeal/dailydeal');
    }

    public function isPending()
    {
        return ($this->getPhase() == 1);
    }

    /**
     * Overrides the getPhase() property getter to "lazy calculate" the phase property
     */
    public function getPhase()
    {
        if ($this->hasData('phase'))
            return $this->getData('phase');
        $phase = $this->_calculatePhase();
        $this->setPhase($phase);
        return $phase;
    }

    /**
     * Caculates the correct phase based on the current date and time
     */
    public function _calculatePhase()
    {
        try {
            //check if this is new daily deal
            if ($this->getStartDatetime() == null)
                return 1; //Pending
            $now = Glace_Dailydeal_Helper_Data::DateTimeToStoreTZ();
            $start = Glace_Dailydeal_Helper_Data::DateTimeToStoreTZ($this->getStartDatetime());
            $end = Glace_Dailydeal_Helper_Data::DateTimeToStoreTZ($this->getEndDatetime());

            if ($start > $now) {
                return 1; //Pending
            } elseif ($end < $now) {
                return 4; //Expried
            } elseif (($start <= $now) && ($now <= $end)) {
                if ($this->getStatus() == 1) {
                    return 2; //In Progress
                } else {
                    return 3; //Paused
                }
            }
        } catch (Exception $ex) {//end try
            Glace_Dailydeal_Helper_Data::LogError($ex);
        }
    }

    /**
     * Dependent function 1
     */
    public function calculateDailydealPrice($originalPrice = null, $discount = null)
    {
        if ($originalPrice === null) {
            $originalPrice = $this->getProduct()->getPrice();
        }
        if ($discount === null)
            $discount = $this->getDiscount();
        switch ($this->getDiscountType()) {
            case 2:
                // by fixed amount from original price
                $price = round($originalPrice - $discount, 2);
                break;
            case 3:
                // to percentage of original price
                $price = round($originalPrice * ($discount / 100), 2);
                break;
            case 4:
                // to new fixed price
                $price = round($discount, 2);
                break;
            case 1:
            default:
                // by percentage of original price
                $price = round($originalPrice * (1 - $discount / 100), 2);
        }
        return $price;
    }

    /**
     * Dependent function 2
     */
    public function getOriginalPrice($website = null, $backend = false)
    {
        $tax_helper = Mage::helper('tax');
        // get groupsale store
        $store = $this->getStore($website);

        // get price by appropriate display type
        if ((!$backend && $tax_helper->getPriceDisplayType($store) == Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX) ||
                ($backend && !Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store)))
            $price = $this->GetProductPrice(false, $website);
        else
            $price = $this->GetProductPrice(true, $website);

        return $price;
    }

    /**
     * Dependent function 3
     * Gets original product price including/excluding tax
     *
     * @param	null|bool $tax - null = default, true = including, false = excluding
     * @param	mixed $store
     * @return	float $price
     */
    public function getProductPrice($tax = null, $website = null)
    {
        $tax_helper = Mage::helper('tax');
        $store = $this->getStore($website);
        $product = $this->getProduct();
        $price = $product->getPrice();
        $priceIncludesTax = $tax_helper->priceIncludesTax($store);
        $percent = $product->getTaxPercent();
        $includingPercent = null;
        $taxClassId = $product->getTaxClassId();

        if ($percent === null && $taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, null, $store);
            $percent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
        }
        if ($priceIncludesTax && $taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $store);
            $includingPercent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
        }
        if (($percent === false || $percent === null) && $priceIncludesTax && !$includingPercent)
            return $price;

        if ($priceIncludesTax)
            $price = $this->_CalcProductPrice($price, $includingPercent, false);
        if ($tax || (($tax === null) && ($tax_helper->getPriceDisplayType($store) != Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX)))
            $price = $this->_CalcProductPrice($price, $percent, true);
        return $price;
    }

    /**
     * Dependent function of getProductPrice function 1
     */
    protected function _CalcProductPrice($price, $percent, $type)
    {
        if ($type)
            return $price * (1 + ($percent / 100));
        else
            return $price - ($price / (100 + $percent) * $percent);
    }

    public function getProduct()
    {
        if ($this->hasData('product'))
            return $this->getData('product');
        $product = Mage::getModel('catalog/product')->load($this->getProductId());
        $this->setProduct($product);
        return $product;
    }

    public function getDailydealPrice($website = null, $backend = false)
    {
        $price = $this->GetOriginalPrice($website, $backend);
        $gs_price = $this->calculateDailydealPrice($price);

        return $gs_price;
    }

    protected function _afterLoad()
    {
        $item = $this;
        if ($item->getData('store_view') != null) {
            $store_view = $item->getData('store_view');
            $temp_store_view = explode(",", $store_view); // 1,2 => array(1,2)
            $item->setData('store_view', $temp_store_view);
        }


        if ($item->getData('order_id') != null) {
            $order_id = $item->getData('order_id');
            $temp_order_id = explode(",", $order_id); // 1,2 => array(1,2)
            $item->setData('order_id', $temp_order_id);
        }
        parent::_afterLoad();
    }

    protected function validate()
    {

        // Check start_date_time < end_date_time
        $start_timestamp = (int) strtotime($this->getData('start_date_time'));
        $end_timestamp = (int) strtotime($this->getData('end_date_time'));

        if ($start_timestamp > $end_timestamp) {
            throw new Exception(Mage::helper('dailydeal')->__("'Active To' must be equal or more than 'Active From'"));
        }

        // Deal have order not allow change product
        $model_origin = Glace_Dailydeal_Model_Dailydeal::getModel()->load($this->getId());
        if ($model_origin->getData('order_id')) {
            if ($this->getData('product_id') != $model_origin->getData('product_id')) {
                throw new Exception(Mage::helper('dailydeal')->__("You can not change product because deal have order"));
            }
        }

        if ($this->getData('status') != Glace_Dailydeal_Model_Status::STATUS_DISABLED) {
            // Check product_id not exist in double deal
            $start_date_time = date('Y-m-d H:i:s', strtotime($this->getData('start_date_time')));
            $end_date_time = date('Y-m-d', strtotime($this->getData('end_date_time')));
             
            $collection = $this->getCollection()
                    ->addFieldToFilter('product_id', $this->getData('product_id'))
                    ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED);
            $collection->getSelect()->where("
                (start_date_time < '{$start_date_time}' AND end_date_time >= '{$start_date_time}') OR
                (start_date_time >= '{$start_date_time}' AND start_date_time <= '{$end_date_time}')
            ");
                
            // Edit deal
            if(($this->getId() != null)){
                $collection->addFieldToFilter('dailydeal_id', array('neq' => $this->getId()));
            }

            if ($collection->count()) {
                throw new Exception(Mage::helper('dailydeal')->__("Have another deal with this product in time.Please select other deal time!"));
            }
        }

		// when save deal but deal's data is empty -> not save
//        if($this->getData('product_id') == ''){
//            throw new Exception(Mage::helper('dailydeal')->__("You must choice a product for deal"));
//        }
    }

    protected function setDataDefault()
    {
        $model_product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
        $this->setData('model_product', $model_product);

        $this->setData('discount_type', 4);                                 // fix prices
        $this->setData('discount', $this->getData('dailydeal_price'));      // fix prices


        if (!$this->checkDealPrice($model_product)) {
            $this->setData('dailydeal_price', '');
        }
        if (!$this->checkDealQuantity($model_product)) {
            $this->setData('deal_qty', '');
        }
    }

    protected function _beforeSave()
    {
        $this->validate();
        $this->setDataDefault();

        $id = Mage::app()->getRequest()->getParam('id');
        if (isset($id)) {
            $this->setDataFollowOldData();
            $this->setDisableProduct();
        }

        $this->setExpire();
        $this->autoSetActive();
        $this->convertStoreViewToString();
        $this->convertOrderIdToString();

        parent::_beforeSave();
    }

    /**
     * auto set active of deal => to view color
     */
    protected function autoSetActive()
    {
        $this->setData('active', $this->getStatusTime());
    }

    /**
     * When edit form, a few field need load default
     */
    protected function setDataFollowOldData()
    {
        $old_deal = Glace_Dailydeal_Model_Dailydeal::getModel();
        $old_deal->load($this->getId());

        $this->setData('order_id', $old_deal->getData('order_id'));
    }

    protected function convertStoreViewToString()
    {
        if(!Mage::app()->isSingleStoreMode()){
            $data = $this->getData();
            $help_toolac = Glace_Dailydeal_Helper_Toolasiaconnect::getInstance();
            $data['store_view'] = $help_toolac->convertStoreViewToString($data['store_view']);

            $this->setData('store_view', $data['store_view']);
        }else{
            $this->setData('store_view', 0);
        }
    }

    protected function convertOrderIdToString()
    {
        $data = $this->getData();
        $help_toolac = Glace_Dailydeal_Helper_Toolasiaconnect::getInstance();
        $data['order_id'] = $help_toolac->convertStoreViewToString($data['order_id']);

        $this->setData('order_id', $data['order_id']);
    }

    /**
     * if disabled product then edit deal.  Only Deal QUEUED or Running be update 'disable_product_after_finish'
     * @return type
     */
    protected function setDisableProduct()
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();
        $model_deal->load($this->getId());
        if ($model_deal->getData('disable_product_after_finish') == Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DONE) {
            $flag_status_time = $this->getStatusTime();
            if ($flag_status_time == Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED || $flag_status_time == STATUS_TIME_DISABLED) {
                $data = $this->getData();
                unset($data['disable_product_after_finish']);
                $this->setData($data);
            }
        }
    }

    /**
     * Add or edit or update sold_qty -> must set field 'expire' follow Product's Type
     */
    protected function setExpire()
    {
        $product_id = $this->getData('product_id');
        $model_product = Mage::getModel('catalog/product')->load($product_id);

        $id = Mage::app()->getRequest()->getParam('id');
        if (isset($id)) {
            // if form edit => get sold_qty form database
            $model_deal_temp = Glace_Dailydeal_Model_Dailydeal::getModel()->load($id);
            if ($model_deal_temp->getId()) {
                $this->setData('sold_qty', $model_deal_temp->getData('sold_qty'));
            }
        }

        $flag_qty = $this->checkDealQty($model_product, $this);
        $expire = $flag_qty ? Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE : Glace_Dailydeal_Model_Status::STATUS_EXPIRE_TRUE;
        $this->setData('expire', $expire);
    }

    /**
     * true : allow buy, false : not buy
     * @return boolean 
     */
    public function checkSoldQty($model_product, $model_deal, $buy_qty)
    {
        $return = false;
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // alow buy
            $return = true;
        }

        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL ||
                $model_product->getData('type_id') == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            $dealqty = $model_deal->getData('deal_qty');
            $soldqty = $model_deal->getData('sold_qty');
            
            if($dealqty == 0){
                $return = true;
            }
            
            if ($buy_qty <= ($dealqty - $soldqty)) {
                // alow buy
                $return = true;
            }
        }

        return $return;
    }

    /**
     * true : quantity > sold, false : quantity < sold
     * @return boolean
     */
    public function checkDealQty($model_product, $model_deal)
    {
        $return = false;
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // alow buy
            $return = true;
        }

        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL ||
                $model_product->getData('type_id') == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            
            $dealqty = $model_deal->getData('deal_qty');
            $soldqty = $model_deal->getData('sold_qty');

            if($dealqty == 0){
                $return = true;
            }
            
            if ($dealqty - $soldqty > 0) {
                // alow buy
                $return = true;
            }
        }
        
        return $return;
    }

    /**
     * true : allow show deal, false : not show deal
     * @return boolean
     */
    public function checkDealQtyToShow($model_product)
    {
        $return = false;
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // alow buy
            $return = false;
        }

        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL ||
                $model_product->getData('type_id') == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            $dealqty = $this->getData('deal_qty');
            $soldqty = $this->getData('sold_qty');

            if($dealqty == 0){
                $return = true;
            }
            
            if ($dealqty - $soldqty > 0) {
                // alow buy
                $return = true;
            }
        }

        return $return;
    }

    /**
     * true : allow view price, false : not view price
     * @return boolean
     */
    public function checkDealPrice($model_product)
    {
        $return = true;
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // not alow view price
            $return = false;
        }

        return $return;
    }

    /**
     * true : product type 1, false product type 0
     * @return boolean
     */
    public function checkDealQuantity($model_product)
    {
        $return = true;
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // alow buy
            $return = false;
        }

        return $return;
    }

    /**
     * get list deals running
     * @return Glace_Dailydeal_Model_Mysql4_Dailydeal_Collection
     */
    public function loadListDeals($condition = array())
    {
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection = $this->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')));

        if ($condition['featured'] == true) {
            $collection->addFieldToFilter('featured', Glace_Dailydeal_Model_Status::STATUS_FEATURED_ENABLED);
        }
        $collection->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                ->addAttributeToSort('start_date_time', 'ASC')
                ->addAttributeToSort('end_date_time', 'ASC');

        $collection->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );

        $collection->getSelect()->where("stock.is_in_stock = " . Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

        return $collection;
    }

    /**
     * get deal running
     * @param int $product_id
     * @return Glace_Dailydeal_Model_Dailydeal
     */
    public function loadByProductId($product_id = null)
    {
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection_deal = $this->getCollection()
                ->addFieldToFilter('product_id', $product_id)
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')))
                ->addFieldToFilter('start_date_time', array('to' => $now))
                ->addFieldToFilter('end_date_time', array('from' => $now))
                ->load();

        if (count($collection_deal->getItems())) {
            $this->setData($collection_deal->getFirstItem()->getData());
        }

        return $this;
    }

    /**
     * Get status active of this (deal). 
     * @return int
     */
    public function getStatusTime()
    {
        $model_deal = $this;
        $timestamp = Mage::getModel('core/date')->timestamp(time());
        $start_date_time = strtotime($model_deal->getData('start_date_time'));
        $end_date_time = strtotime($model_deal->getData('end_date_time'));
        $expire = $model_deal->getData('expire');

        // 1 DISABLE
        if ($model_deal->getData('status') == Glace_Dailydeal_Model_Status::STATUS_DISABLED) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED;
        }

        // 2 ENABLE, RUNNING
        elseif ($start_date_time <= $timestamp AND $timestamp <= $end_date_time AND $expire == Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING;
        }

        // 3 ENDED
        elseif ($end_date_time < $timestamp || $expire == Glace_Dailydeal_Model_Status::STATUS_EXPIRE_TRUE) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED;
        }

        // 4 QUEUED
        if ($timestamp < $start_date_time) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_QUEUED;
        }

        // Default 
        return Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED;
    }

    public function insertOrderId($order_id)
    {
        $temp_order_ids = $this->getData('order_id');
        $temp_order_ids[] = $order_id;

        $order_ids = array_filter($temp_order_ids);

        $this->setData('order_id', $order_ids);

        return $this;
    }

    public function getLimitStartDateTime($deal_scheduler_id, $index)
    {
        $result = '';
        $collection = $this->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id)
                ->addFieldToFilter('thread', $index)
                ->addAttributeToSort('end_date_time', 'DESC');

        if (count($collection->getItems())) {
            $result = $collection->getFirstItem()->getData('end_date_time');
        }

        return $result;
    }

    /**
     * Check : next many days,  system have deal : queue, running, ended
     */
    public function isHaveDealRunning($condition)
    {
        $result = false;

        $collection = $this->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED);

        if (isset($condition['now']) && isset($condition['end_date_time'])) {
            $collection->addFieldToFilter('end_date_time', array('from' => $condition['now']));
        }

        if ($collection->count()) {
            $result = true;
        }

        return $result;
    }

    public function processValueDiscountSaveBought($param = array())
    {

        if (isset($param['model_product'])) {
            $model_product = $param['model_product'];
        } else {
            // load by product_id
        }

        $product_price = round($model_product['price'], 2);
        $deal_price = $this->getData('dailydeal_price');
        $you_save = $product_price - $this->getData('dailydeal_price');
        $one_percent_price = $product_price / 100;
        $percent = round(($product_price - $deal_price ) / $one_percent_price, 2);

        if ($this->getId()) {
            $data = array();
            $data['discount'] = $percent . '%';
            $data['you_save'] = Mage::helper('core')->currency($you_save);
            $data['bought'] = $this->getData('sold_qty');
            $this->setData('value_discount_save_bought', $data);
        }
    }

    public function setMinPriceFollowProductType($model_product, $column = 'min_price')
    {
        if ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $price = Glace_Dailydeal_Model_Product::getMinPriceProductGrouped($model_product);
            $model_product->setData($column, $price);
        } elseif ($model_product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $price = Glace_Dailydeal_Model_Product::getMinPriceProductBundle($model_product);
            $model_product->setData($column, $price);
        }
    }

}