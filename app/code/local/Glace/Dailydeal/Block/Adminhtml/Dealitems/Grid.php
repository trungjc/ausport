<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('dealitemsGrid');
        $this->setDefaultSort('dailydeal_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        
        // update field 'Active' for all Deal
        Glace_Dailydeal_Model_Business::autoUpdateDealActive();
        
        $collection = Mage::getModel('dailydeal/dailydeal')->getCollection();
        $collection->getSelect()->joinLeft(
                array( 'table_dealscheduler' => $collection->getTable('dealscheduler')),
                'main_table.deal_scheduler_id = table_dealscheduler.deal_scheduler_id',
                array('table_dealscheduler.title')
            );
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('dailydeal_id', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('adminhtml')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'dailydeal_id',
            'filter_index'=>'main_table.dailydeal_id'
        ));
        $this->addColumn('cur_product', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('catalog')->__('Product Name'),
            'align' => 'left',
            'index' => 'cur_product',
            'filter_index'=>'main_table.cur_product'
        ));
        $this->addColumn('product_sku', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('catalog')->__('SKU'),
            'width' => '130px',
            'align' => 'left',
            'index' => 'product_sku',
            'filter_index'=>'main_table.product_sku'
        ));
        $this->addColumn('start_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Active From'),
            'width' => '130px',
            'align' => 'left',
            'index' => 'start_date_time',
            'type'=>'datetime',
            'format' => 'y-MM-dd H:m:s',
            'filter_index'=>'main_table.start_date_time'
        ));
        $this->addColumn('end_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Active To'),
            'width' => '130px',
            'align' => 'left',
            'index' => 'end_date_time',
            'type'=>'datetime',
            'format' => 'y-MM-dd H:m:s',
            'filter_index'=>'main_table.end_date_time'
        ));
        $store = $this->_getStore();
        $this->addColumn('product_price', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Regular Price'),
            'type' => 'price',
            'align' => 'left',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'product_price',
            'filter_index'=>'main_table.product_price'
        ));
        $this->addColumn('dailydeal_price', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Price'),
            'type' => 'price',
            'align' => 'left',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'dailydeal_price',
            'filter_index'=>'main_table.dailydeal_price'
        ));
        $this->addColumn('deal_qty', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Qty'),
            'width' => '50px',
            'index' => 'deal_qty',
            'filter_index'=>'main_table.deal_qty'
        ));
        $this->addColumn('sold_qty', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Sold Qty'),
            'width' => '50px',
            'index' => 'sold_qty',
            'filter_index'=>'main_table.sold_qty'
        ));
        $this->addColumn('featured', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Featured'),
            'index' => 'featured',
            'type' => 'options',
            'options' => Glace_Dailydeal_Model_Status::getFeaturedOptionArray(),
            'filter_index'=>'main_table.featured'
        ));
        $this->addColumn('title', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Multi Deals'),
            'align' => 'left',
            'width' => '70px',
            'index' => 'title',
            'filter_index'=>'table_dealscheduler.title'
        ));
        $this->addColumn('active', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'active',
            'type' => 'options',
            'options' => Glace_Dailydeal_Model_Status::getStatusTimeOptionArray(),
            'renderer' => new Glace_Dailydeal_Block_Adminhtml_Renderer_Active(),
            'filter_index'=>'main_table.active'
        ));

        $this->addColumn('action', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('backup')->__('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('catalog')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('dailydeal')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('dailydeal')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('dailydeal_id');
        $this->getMassactionBlock()->setFormFieldName('dailydeal');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('adminhtml')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('dailydeal/status')->getOptionArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('catalog')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('dailydeal')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        $ret = $this->getUrl('dailydeal/adminhtml_dealitems/index', array(
            'index' => $this->getIndex(),
            '_current' => true,
                ));
        return $ret;
    }

}