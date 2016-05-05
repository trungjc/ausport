<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Product_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function getFormHtml()
    {
        $product_block_brid = $this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_edit_product_grid');
        return parent::getFormHtml() . $product_block_brid->getHtml();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        return parent::_prepareForm();
    }

}