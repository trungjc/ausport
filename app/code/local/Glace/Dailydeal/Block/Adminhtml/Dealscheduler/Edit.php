<?php

class Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'dailydeal';
        $this->_controller = 'adminhtml_dealscheduler';

        $this->_updateButton('save', 'label', '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Save Multi Deals'));
        $this->_updateButton('delete', 'label', '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Delete Multi Deals'));

        $this->_addButton('save_apply', array(
            'class' => 'save',
            'label' => '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Save & Start Multi Deals'),
            'onclick' => "$('rule_auto_generate_deal').value=1; editForm.submit()",
        ));
        
        $this->_addButton('saveandcontinue', array(
            'label' => '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
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
    }

    public function getHeaderText()
    {
        if (Mage::registry('dealscheduler_data') && Mage::registry('dealscheduler_data')->getId()) {
            return '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__("Edit Multi Deals '#%s'", $this->htmlEscape(Mage::registry('dealscheduler_data')->getTitle()));
        } else {
            return '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Add Multi Deals');
        }
    }

}