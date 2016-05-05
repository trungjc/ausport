<?php

class Glace_Dailydeal_Block_Adminhtml_System_Config_Messagequantity extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return '
            <button type="button" title="' . $this->__("Get default messages") . '" onclick="get_messages()">' . $this->__("Get default messages") . '</button>
        ';
    }

}