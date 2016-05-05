<?php
class Glace_Dailydeal_Block_Adminhtml_Dealitems extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_dealitems';
    $this->_blockGroup = 'dailydeal';
    //$name_action=Mage::app()->getRequest()->getRouteName();
    //echo $name_action;
    $this->_headerText = '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Deals Manage');
    $this->_addButtonLabel = '<i class="fa fa-hand-o-down fa-2x"></i>'.Mage::helper('dailydeal')->__('Add Deal');
    parent::__construct();
  }
}