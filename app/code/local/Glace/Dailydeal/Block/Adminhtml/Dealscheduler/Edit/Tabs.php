<?php

class Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('dailydeal_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Multi Deals'));
    }

    protected function _beforeToHtml()
    {
        
        $this->addTab('conf_section', array(
            'label' => '<i class="fa fa-thumbs-o-up fa-2x"></i>'.Mage::helper('dailydeal')->__('Settings'),
            'title' => Mage::helper('dailydeal')->__('Settings'),
            'content' => $this->getLayout()->createBlock( new Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Conf_Form() )->toHtml(),
        ));

        $this->addTab('list_product', array(
            'label' => '<i class="fa fa-thumbs-o-up fa-2x"></i>'.Mage::helper('dailydeal')->__('Select Product(s)'),
            'title' => Mage::helper('dailydeal')->__('Select Product(s)'),
            'url'       => $this->getUrl('*/*/product', array('_current' => true)),
//            'content' => $this->getLayout()->createBlock( new Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Product_Form() )->toHtml(),
            'class'     => 'ajax',
        ));
        
        return parent::_beforeToHtml();
    }
}