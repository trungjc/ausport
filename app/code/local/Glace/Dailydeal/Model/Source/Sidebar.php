<?php
class Glace_Dailydeal_Model_Source_Sidebar
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('dailydeal')->__('Left Bar')),
            array('value' => 2, 'label'=>Mage::helper('dailydeal')->__('Right Bar')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Disabled')),
        );
    }
}