<?php

class Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_selection');
        $this->setDefaultSort('id');

        $this->setUseAjax(true);

        if ($this->_getProduct()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }

        $this->setVarNameFilter('product_filter');
        $this->setRowClickCallback("");
        
    }

    protected function _beforeToHtml()
    {
        $this->setId($this->getId() . '_' . $this->getIndex());
        $this->getChild('reset_filter_button')->setData('onclick', $this->getJsObjectName() . '.resetFilter()');
        $this->getChild('search_button')->setData('onclick', $this->getJsObjectName() . '.doFilter()');
        return parent::_beforeToHtml();
    }
    
    protected function _toHtml(){
        if($this->getData('comment_deal') == 1){
            $html = Mage::helper('dailydeal')->__('<p class="note"><span class="required">(*)</span> You can override defaults set at tab Settings. Leave blank to use default values.</p>');
            $html .= Mage::helper('dailydeal')->__('<p class="note"><span class="required">(**)</span> Use to set position for sequentially generated deals</p>');
        }
        return parent::_toHtml() . $html;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');

        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('visibility', array('gt' => '1'))
                ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->addFieldToFilter('is_in_stock', 1);
        if (!empty($id)) {
            $collection->joinField('deal_time', 'dailydeal/dealschedulerproduct', 'deal_time', 'product_id=entity_id', '{{table}}.deal_scheduler_id=' . $id, 'left');
            $collection->joinField('deal_price', 'dailydeal/dealschedulerproduct', 'deal_price', 'product_id=entity_id', '{{table}}.deal_scheduler_id=' . $id, 'left');
            $collection->joinField('deal_qty', 'dailydeal/dealschedulerproduct', 'deal_qty', 'product_id=entity_id', '{{table}}.deal_scheduler_id=' . $id, 'left');
            $collection->joinField('deal_position', 'dailydeal/dealschedulerproduct', 'deal_position', 'product_id=entity_id', '{{table}}.deal_scheduler_id=' . $id, 'left');
        }
        // $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));


        $this->addColumn('prd_entity_id', array(
            'header' => Mage::helper('adminhtml')->__('ID'),
            'sortable' => true,
            'width' => '30px',
            'index' => 'entity_id'
        ));

        $this->addColumn('prd_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'width' => '50px',
            'index' => 'name',
            'column_css_class' => 'name'
        ));

        $this->addColumn('prd_type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_type')->getOptionArray(),
        ));
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));
        
        $this->addColumn('prd_sku', array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'index' => 'sku',
                'column_css_class' => 'sku'
            ));

        $this->addColumn('prd_price', array(
            'header' => Mage::helper('sales')->__('Price'),
            'align' => 'center',
            'type' => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'rate' => $this->_getStore()->getBaseCurrency()->getRate($this->_getStore()->getBaseCurrency()->getCode()),
            'index' => 'price'
        ));

        $this->addColumn('prd_qty', array(
            'header' => Mage::helper('catalog')->__('Qty'),
            'type' => 'number',
            'index' => 'qty',
        ));

        $this->addColumn('deal_time', array(
            'header' => Mage::helper('dailydeal')->__('Deal Time (hrs)<span class="required">*</span>'),
            'name' => 'deal_time',
            'type' => 'number',
            'width' => '50px',
            'validate_class' => 'validate-number',
            'index' => 'deal_time',
            'editable' => true,
        ));

        $this->addColumn('deal_price', array(
            'header' => Mage::helper('dailydeal')->__('Deal Price (Fixed)<span class="required">*</span>'),
            'name' => 'deal_price',
            'type' => 'number',
            'width' => '50px',
            'validate_class' => 'validate-number',
            'index' => 'deal_price',
            'editable' => true,
        ));

        $this->addColumn('deal_qty', array(
            'header' => Mage::helper('dailydeal')->__('Deal Quantity (Fixed)<span class="required">*</span>'),
            'name' => 'deal_qty',
            'type' => 'number',
            'width' => '50px',
            'validate_class' => 'validate-number',
            'index' => 'deal_qty',
            'editable' => true,
        ));

        $this->addColumn('deal_position', array(
            'header' => Mage::helper('dailydeal')->__('Position<span class="required">**</span>'),
            'name' => 'deal_position',
            'type' => 'number',
            'width' => '50px',
            'validate_class' => 'validate-number',
            'index' => 'deal_position',
            'editable' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridProduct', array('_current' => true));
    }

    protected function _getProduct()
    {
        return Mage::registry('products');
    }

    protected function _getStore()
    {
        return Mage::app()->getStore($this->getRequest()->getParam('store'));
    }

    /**
     * Array product is checked
     * array(
     *  0    => 16
     *  1    => 17
     * )
     */
    protected function _getSelectedProducts()
    {
        $products = array_keys($this->_getProduct());
        return $products;
    }
    
    /**
     * Data set to hidden input
     * @return array (
     *  16 = array(
     *          deal_price  = 1,
     *          deal_qty    = 2,
     *          deal_time   = 3,
     *      )
     * )
     */
    public function data_callback(){
        $this->setData('comment_deal', 1);
        return $this->_getProduct();
    }
    

}