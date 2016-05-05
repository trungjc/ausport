<?php

/**
 * Mage World
 *
 * NOTICE OF LICENSE

 * @category    Glace
 * @package     Glace_Dailydeal
 * @copyright   Copyright (c) 2012 Mage World (http://www.mageworld.com)

 */

/**
 * Adminhtml Dailyschedule Grid Block
 *
 * @category   Glace
 * @package    Glace_Dailydeal
 * @author     Magento Developer <chinhbt@asiaconnect.com.vn>
 */
//Lay tu Mage_Adminhtml_Block_Report_Product_Grid
class Glace_Dailydeal_Block_Adminhtml_Dailyschedule_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_storeSwitcherVisibility = true;
    protected $_dateFilterVisibility = true;
    protected $_exportVisibility = true;
    //
    protected $_filters = array();
    //check value of 2 field report_from va report_to    
    protected $_defaultFilters = array(
        'report_from' => '',
        'report_to' => '',
    );

    /**
     * stores current currency code
     */
    protected $_currentCurrencyCode = null;
    protected $_errors = array();

    /**
     * Contain value time filter -> file template will get value
     */
    protected $report_from;

    /**
     * Contain value time filter -> file template will get value
     */
    protected $report_to;

    /**
     * Initialize container block settings
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('gridDailyscheduleList');
        $this->setDefaultSort('id');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setTemplate('glace_dailydeal/dailyschedule/grid.phtml');

        $this->setUseAjax(false);
    }

    /**
     * 
     * Prepare layout for grid
     */
    protected function _prepareLayout() {
        $this->setChild('store_switcher', $this->getLayout()->createBlock('adminhtml/store_switcher')
                        ->setUseConfirm(false)
                        ->setSwitchUrl($this->getUrl('*/*/*', array('store' => null)))
                        ->setTemplate('report/store/switcher.phtml')
        );
        $this->setChild('show_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('dailydeal')->__('Show deals'),
                            'onclick' => $this->getRefreshButtonCallback(),
                            'class' => 'task'
                        ))
        );
        parent::_prepareLayout();
        return $this;
    }

    /**
     * 
     * Prepare collection object for grid
     */
    protected function _prepareCollection() {

        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (empty($filter)) {
            // Default filter
            $now = Mage::getModel('core/date')->timestamp(time());
            $end = strtotime('+ 29 days', $now);

            $data['report_from'] = date('m-d-Y', $now);
            $data['report_to'] = date('m-d-Y', $end);

            $this->_setFilterValues($data);
        } elseif (is_string($filter)) {
            // Choiced filter
            $data = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (0 != sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }

        $collection = Mage::getModel('dailydeal/dailydeal')->getCollection();
        if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
            try {
                /**
                 * validate from and to date
                 */
                $from = $this->getLocale()->date($this->getFilter('report_from'), 'MM/dd/yy', null, false);
                $to = $this->getLocale()->date($this->getFilter('report_to'), 'MM/dd/yy', null, false);
                $collection->setInterval($from, $to);
            } catch (Exception $ex) {
                Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            }
        }

        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = array();
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys(Mage::app()->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);


        $collection->setStoreIds($storeIds);
        // Zend_Debug::dump($collection);die();

        $this->setCollection($collection);

        Mage::dispatchEvent('adminhtml_widget_grid_filter_collection', array(
            'collection' => $this->getCollection(),
            'filter_values' => $this->_filterValues
        ));


        //return $this;//die();
    }

    /**
     * Begin
     * Append of _prepareCollection function to filter
     */
    protected function _setFilterValues($data) {
        // Set value for input 'From Date' and 'To Date'
        if (isset($data['report_from'])) {
            $this->report_from = $data['report_from'];
        }
        if (isset($data['report_to'])) {
            $this->report_to = $data['report_to'];
        }

        foreach ($data as $name => $value) {
            $this->setFilter($name, $data[$name]);
        }
        return $this;
    }

    //Append of _setFilterValues function
    public function setFilter($name, $value) {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    public function getFilter($name) {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return ($this->getRequest()->getParam($name)) ? htmlspecialchars($this->getRequest()->getParam($name)) : '';
        }
    }

    /**
     * End
     */

    /**
     * Set visibility of store switcher
     *
     * @param boolean $visible
     */
    public function setStoreSwitcherVisibility($visible = true) {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return visibility of store switcher
     *
     * @return boolean
     */
    public function getStoreSwitcherVisibility() {
        return $this->_storeSwitcherVisibility;
    }

    /**
     * Return store switcher html
     *
     * @return string
     */
    public function getStoreSwitcherHtml() {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Set visibility of date filter
     *
     * @param boolean $visible
     */
    public function setDateFilterVisibility($visible = true) {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return visibility of date filter
     *
     * @return boolean
     */
    public function getDateFilterVisibility() {
        return $this->_dateFilterVisibility;
    }

    public function getDateFormat() {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Return refresh button html
     */
    public function getRefreshButtonHtml() {
        return $this->getChildHtml('show_button');
    }

    /**
     * onlick event for refresh button to show alert if fields are empty
     *
     * @return string
     */
    public function getRefreshButtonCallback() {
        return "{$this->getJsObjectName()}.doFilter();";
        return "if ($('period_date_to').value == '' && $('period_date_from').value == '') {alert('" . Mage::helper('dailydeal')->__('Please specify at least start or end date.') . "'); return false;}else {$this->getJsObjectName()}.doFilter();";
    }

    /**
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::getEmptyText()
     */
    public function getEmptyText() {
        return Mage::helper('dailydeal')->__('No deal found in this day.');
    }

    /**
     * Retrieve errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Retrieve correct currency code for select website, store, group
     *
     * @return string
     */
    public function getCurrentCurrencyCode() {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $store = $this->getRequest()->getParam('store');
                $this->_currentCurrencyCode = Mage::app()->getStore($store)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('website')) {
                $website = $this->getRequest()->getParam('website');
                $this->_currentCurrencyCode = Mage::app()->getWebsite($website)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('group')) {
                $group = $this->getRequest()->getParam('group');
                $this->_currentCurrencyCode = Mage::app()->getGroup($group)->getWebsite()->getBaseCurrencyCode();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            }
        }
        return $this->_currentCurrencyCode;
    }

    /**
     * Set visibility of export action
     *
     * @param boolean $visible
     */
    public function setExportVisibility($visible = true) {
        $this->_exportVisibility = $visible;
    }

    /**
     * Return visibility of export action
     *
     * @return boolean
     */
    public function getExportVisibility() {
        return $this->_exportVisibility;
    }

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addExportType($url, $label) {
        $this->_exportTypes[] = new Varien_Object(
                        array(
                            'url' => $this->getUrl($url, array(
                                '_current' => true,
                                'filter' => $this->getParam($this->getVarNameFilter(), null)
                                    )
                            ),
                            'label' => $label
                        )
        );
        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Glace_Dailydeal_Block_Adminhtml_Daily_Dealschedule_Grid
     */
    protected function _prepareColumns() {

        $this->addColumn('cur_product', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('catalog')->__('Product Name'),
            'align' => 'left',
            'index' => 'cur_product',
        ));
        $this->addColumn('product_sku', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('catalog')->__('SKU'),
            'align' => 'left',
            'index' => 'product_sku',
        ));
        $this->addColumn('start_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Active From'),
            'align' => 'left',
            'index' => 'start_date_time',
        ));
        $this->addColumn('end_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Active To'),
            'align' => 'left',
            'index' => 'end_date_time',
        ));
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = Mage::app()->getStore($storeId);
        $this->addColumn('product_price', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Regular Price'),
            'type' => 'price',
            'align' => 'left',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'product_price',
        ));
        $this->addColumn('dailydeal_price', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Price'),
            'type' => 'price',
            'align' => 'left',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'dailydeal_price'
        ));
        $this->addColumn('deal_qty', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Qty'),
            'width' => '50px',
            'index' => 'deal_qty',
        ));
        $this->addColumn('featured', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Featured'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'featured',
            'type' => 'options',
            'options' => Glace_Dailydeal_Model_Status::getFeaturedOptionArray(),
        ));

        $this->addColumn('status', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Glace_Dailydeal_Model_Status::getOptionArray(),
        ));

        $this->addColumn('action', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('backup')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('catalog')->__('Edit'),
                    'url' => array('base' => '*/adminhtml_dealitems/edit/',),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        $this->addExportType('*/*/exportSoldCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportSoldExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve locale
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale() {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

}