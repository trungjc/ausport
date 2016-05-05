<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Report_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('dailydeal_form', array(
            'legend' => Mage::helper('dailydeal')->__('Deal Report')
                ));

        $fieldset->addField('impression', 'label', array(
            'name' => 'impression',
            'title' => Mage::helper('dailydeal')->__('Number of Impressions'),
            'label' => Mage::helper('dailydeal')->__('Number of Impressions'),
        ));

        $fieldset->addField('sold_qty', 'label', array(
            'name' => 'sold_qty',
            'title' => Mage::helper('dailydeal')->__('Sold Qty'),
            'label' => Mage::helper('dailydeal')->__('Sold Qty'),
        ));
        
        $block = $this->getLayout()->createBlock(new Glace_Dailydeal_Block_Adminhtml_Sales_Order_Grid());
        $this->setChild('form_after', $block);

        if (Mage::getSingleton('adminhtml/session')->getDealitemsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getDealitemsData());
            Mage::getSingleton('adminhtml/session')->setDealitemsData(null);
        } elseif (Mage::registry('dealitems_data')) {

            $id = $this->getRequest()->getParam("id");
            if (empty($id)) {
                
            } else {
                $form->setValues($this->setEditFormDefaultValue());
            }
        }

        return parent::_prepareForm();
    }
    
    protected function setEditFormDefaultValue()
    {
        $data = Mage::registry('dealitems_data')->getData();
        return $data;
    }

}