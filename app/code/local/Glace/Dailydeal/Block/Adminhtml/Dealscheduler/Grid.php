<?php

class Glace_Dailydeal_Block_Adminhtml_Dealscheduler_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('dealschedulerGrid');
        $this->setDefaultSort('dailydeal_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Glace_Dailydeal_Model_Dealscheduler::getInstance()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('deal_scheduler_id', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('adminhtml')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'deal_scheduler_id',
        ));

        $this->addColumn('title', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Title Multi Deals'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'title',
        ));
        
        $this->addColumn('deal_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Duration Cycle (hours)'),
            'width' => '80px',
            'index' => 'deal_time',
            'type' => 'number',
        ));

        $this->addColumn('deal_price', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Discount'),
            'align' => 'left',
            'index' => 'deal_price',
        ));
        
        $this->addColumn('deal_qty', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Quantity'),
            'index' => 'deal_qty',
        ));
        
//        $this->addColumn('number_day', array(
//            'header' => Mage::helper('dailydeal')->__('Number of days'),
//            'width' => '80px',
//            'align' => 'left',
//            'index' => 'number_day',
//            'type' => 'number',
//        ));
        
        $this->addColumn('number_deal', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('# of Multi Deals'),
            'width' => '80px',
            'align' => 'left',
            'index' => 'number_deal',
            'type' => 'number',
        ));
        
        $this->addColumn('start_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('From Date'),
            'width' => '130px',
            'align' => 'left',
            'index' => 'start_date_time',
            'type'=>'datetime',
            'format' => 'y-MM-dd H:m:s',
        ));
        
        $this->addColumn('end_date_time', array(
            'header' => '<i class="fa fa-share fa-2x"></i>'.Mage::helper('dailydeal')->__('To Date'),
            'width' => '130px',
            'align' => 'left',
            'index' => 'end_date_time',
            'type'=>'datetime',
            'format' => 'y-MM-dd H:m:s',
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
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
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

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        $return = $this->getUrl('dailydeal/adminhtml_dealscheduler/index', array(
            'index' => $this->getIndex(),
            '_current' => true,
                ));
        return $return;
    }
}