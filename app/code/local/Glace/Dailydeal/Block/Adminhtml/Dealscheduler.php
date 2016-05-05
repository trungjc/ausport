<?php

class Glace_Dailydeal_Block_Adminhtml_Dealscheduler extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_dealscheduler';
        $this->_blockGroup = 'dailydeal';
        $this->_headerText = '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Multi Deals Manage');
        $this->_addButtonLabel = '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Add Multi Deals');
//        $this->_addButton('refreshdeal', array(
//            'label' => Mage::helper('dailydeal')->__('Generate Deals'),
//            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/applyGenerateDeal') . '\')',
//        ));
        parent::__construct();
    }

}