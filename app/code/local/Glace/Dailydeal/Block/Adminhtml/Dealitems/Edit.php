<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'dailydeal';
        $this->_controller = 'adminhtml_dealitems';
        if (Mage::getSingleton('adminhtml/session')->getFlag() == 'dailyschedule')
        $this->_updateButton('back', 'onclick',"setLocation('" . $this->getUrl('*/adminhtml_dailyschedule/days/')."')");
        
        $this->_updateButton('save', 'label', '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Save deal'));
        $this->_updateButton('delete', 'label', '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Delete deal'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('test_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'test_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'test_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        
        if( Mage::registry('dealitems_data') && Mage::registry('dealitems_data')->getId() ) {
            $this->_formScripts[] = "
                window.onload = function () {
                    jumpDealInformationTag();
                }
        ";
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('dealitems_data') && Mage::registry('dealitems_data')->getId() ) {
            return '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__("Edit Deal '%s'", $this->htmlEscape(Mage::registry('dealitems_data')->getCurProduct()));
        } else {
            return '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Add New Deal');
        }
    }
}