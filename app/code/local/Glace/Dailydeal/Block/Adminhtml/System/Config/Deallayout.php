<?php

class Glace_Dailydeal_Block_Adminhtml_System_Config_Deallayout extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return '
        <div style="font-size:13px;margin-left:-205px;margin-top:5px;margin-bottom:5px;"><b><u>' . $this->__("Daily Deal Page Layout") . '</u></b></div>
        ';
    }

}