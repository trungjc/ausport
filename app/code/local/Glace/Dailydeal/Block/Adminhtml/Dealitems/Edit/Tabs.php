<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('dailydeal_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Deal Information'));
    }

    protected function _beforeToHtml()
    {
        
        $this->addTab('list_product', array(
            'label' => '<i class="fa fa-thumbs-o-up fa-2x"></i>'.Mage::helper('dailydeal')->__('Select Product'),
            'title' => Mage::helper('dailydeal')->__('Select Product'),
            'content' => $this->getLayout()->createBlock( new Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Product_Form() )->toHtml()
        ));

        $this->addTab('conf_section', array(
            'label' => '<i class="fa fa-thumbs-o-up fa-2x"></i>'.Mage::helper('dailydeal')->__('Information'),
            'title' => Mage::helper('dailydeal')->__('Information'),
            'content' => $this->getLayout()->createBlock( new Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Conf_Form() )->toHtml(),
        ));

        $id = $this->getRequest()->getParam("id");
        if(isset($id)){
            $this->addTab('report', array(
                'label' => '<i class="fa fa-thumbs-o-up fa-2x"></i>'.Mage::helper('dailydeal')->__('Reports'),
                'title' => Mage::helper('dailydeal')->__('Reports'),
                'content' => $this->getLayout()->createBlock(new Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Report_Form())->toHtml(),
            ));
        }
            
        return parent::_beforeToHtml();
    }
}