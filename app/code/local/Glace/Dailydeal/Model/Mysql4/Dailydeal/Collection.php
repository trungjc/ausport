<?php

class Glace_Dailydeal_Model_Mysql4_Dailydeal_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Intervals
     *
     * @var int
     */
    protected $_intervals;

    /**
     * Array of store ids
     *
     * @var array
     */
    protected $_storeIds;

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Get store ids
     *
     * @return arrays
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * Get size, ham nay tam thoi chua dung den
     *
     * @return int
     */
    public function getSigze()
    {
        return count($this->getIntervals());
    }

    /**
     * Set interval
     *
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Resource_Report_Collection
     */
    public function setInterval($from, $to)
    {
        $this->_from = $from;
        $this->_to = $to;

        return $this;
    }

    /**
     * Get intervals
     *
     * @return unknown
     */
    public function getIntervals()
    {
        if (!$this->_intervals) {
            $this->_intervals = array();
            if (!$this->_from && !$this->_to) {
                return $this->_intervals;
            }
            $dateStart = new Zend_Date($this->_from);
            $dateEnd = new Zend_Date($this->_to);
            $t = array();
            while ($dateStart->compare($dateEnd) <= 0) {
                $t['title'] = $dateStart->toString(Mage::app()->getLocale()->getDateFormat());
                $t['start'] = $dateStart->toString('yyyy-MM-dd HH:mm:ss');
                $t['end'] = $dateStart->toString('yyyy-MM-dd 23:59:59');
                $dateStart->addDay(1);
                $this->_intervals[$t['title']] = $t;
            }
        }
        return $this->_intervals;
    }

    /**
     * get report full
     *
     * @param int $from
     * @param int $to
     * @return unknown
     */
    public function getReportFull($from, $to)
    {
        return $this->_model->getReportFull($this->timeShift($from), $this->timeShift($to));
    }

    /**
     * Get report
     *
     * @param int $from
     * @param int $to
     * @return Varien_Object
     */
    public function getDeal($from, $to)
    {
        return $this->getDaily($this->timeShift($from), $this->timeShift($to))
                        ->addFieldToFilter('start_date_time', array('from' => $from, 'to' => $to));
    }

    public function getDaily($from, $to)
    {
        return $this->setStoreIds($this->getStoreIds());
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('dailydeal/dailydeal');
    }

    /**
     * get deal follow $product_id
     * @param int $productid
     * @return Glace_Dailydeal_Model_Dailydeal
     */
    public function loadcurrentdeal($productid = null)
    {
        $deal = null;
        
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        
        $this   ->addFieldToFilter('product_id', $productid)
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view',array(array('like'=>'%'. Mage::app()->getStore()->getId() .'%'),array('like'=>'0')))
                ->addFieldToFilter('start_date_time', array('to' => $now))
                ->addFieldToFilter('end_date_time', array('from' => $now))
                ->load();
        
        if (count($this->getItems())) {
            $deal = $this->getFirstItem();
        }
        
        return $deal;
    }

    public function loadmoredeal($productid = null)
    {
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $query = $this->addFieldToFilter('status', 1)
                ->addFieldToFilter('product_id', $productid)
                ->addFieldToFilter('end_date_time', array('from' => $now));
        $query->getSelect()->where("deal_qty > sold_qty");
        $query->load();
        $active_dailydeal = null;
        if (count($this->getItems())) {
            $active_dailydeal = $this->getFirstItem();
        }
        return $active_dailydeal;
    }

    public function loadDailydeal($product_sku = null, $dailydeal_id = null)
    {
        if (method_exists($this, 'addFieldToSelect'))
            $this->addFieldToSelect('*');
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $query = $this->addFieldToFilter('start_date_time', array('to' => $now))
                ->addFieldToFilter('end_date_time', array('from' => $now))
                ->addFieldToFilter('status', 1);
        if ($product_sku !== null)
            $query->addFieldToFilter('product_sku', $product_sku);
        if ($dailydeal_id !== null)
            $query->addFieldToFilter('dailydeal_id', $dailydeal_id);

        $query->load();
        $active_dailydeal = null;
        if (count($this->getItems())) {
            $active_dailydeal = $this->getFirstItem();
        }
    }

    public function getActiveDailydeal($product = null)
    {
        if (gettype($product) != 'object')
            return null;
        if ($product->hasGlace_Dailydeal())
            return $product->getGlace_Dailydeal();
        $active_dailydeal = $this->loadDailydeal($product->getSku());
        $product->setGlace_Dailydeal($active_dailydeal);
        return $active_dailydeal;
    }

    protected $_grid;

    public function setGrid($grid)
    {
        $this->_grid = $grid;
        return $this;
    }

    protected $_loadProducts;

    public function setLoadProducts($bool)
    {
        $this->_loadProducts = $bool ? true : false;
        return $this;
    }

    protected $_rawDates;

    public function setRawDates($bool)
    {
        $this->_rawDates = $bool ? true : false;
        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->order($attribute . " " . $dir);
        return $this;
    }

    protected function _afterLoad()
    {
        foreach ($this as $item) {
            $store_view = $item->getData('store_view');
            $temp_store_view = explode(",", $store_view); // 1,2 => array(1,2)
            $item->setData('store_view', $temp_store_view);

            
            $order_id = $item->getData('order_id');
            $temp_order_id = explode(",", $order_id); // 1,2 => array(1,2)
            $item->setData('order_id', $temp_order_id);
        }

        parent::_afterLoad();
    }
    
    public function getConfigSortBy(){
        $sort = Glace_Dailydeal_Helper_Data::getConfigSortBy();
        if($sort == Glace_Dailydeal_Model_Source_Sortby::SORT_BY_FEATURED_ENDDATETIME ){
            $this->addAttributeToSort('featured', 'ASC')
                 ->addAttributeToSort('end_date_time', 'ASC');
        }elseif($sort == Glace_Dailydeal_Model_Source_Sortby::SORT_BY_RANDOM){
            $this->getSelect()->order('rand()');
        }elseif($sort == Glace_Dailydeal_Model_Source_Sortby::SORT_BY_FEATURED_RANDOM){
            $this->addFieldToFilter('featured', Glace_Dailydeal_Model_Status::STATUS_FEATURED_ENABLED);
            $this->getSelect()->order('rand()');
        }
        return $this;
    }
    
    /**
     * Filter product status
     */
    public function addProductStatusFilter( $store_id, $enable = 1 ){
        $store_id_all = 0;
        $code_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'status')->getId();
        
        $prefix = Mage::getConfig()->getTablePrefix();
        
        $this->getSelect()->joinInner(
            array( 'at_status_default' => $prefix .'catalog_product_entity_int'),
            "(main_table.product_id = at_status_default.entity_id) AND (at_status_default.attribute_id = {$code_id}) AND (at_status_default.store_id = {$store_id_all})",
            array('at_status.value')
        );
        $this->getSelect()->joinLeft(
            array( 'at_status' => $prefix . 'catalog_product_entity_int'),
            "(main_table.product_id = at_status.entity_id) AND (at_status.attribute_id = {$code_id}) AND (at_status.store_id = {$store_id})",
            array('at_status.value')
        );

        $this->getSelect()->where(" (IF(at_status.value_id > 0, at_status.value, at_status_default.value) = '1')");
        
        return $this;
    }
    
}