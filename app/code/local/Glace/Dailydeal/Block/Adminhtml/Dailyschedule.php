<?php
/**
 * Mage World
 *
 * NOTICE OF LICENSE

 * @category    Glace
 * @package     Glace_Dailydeal
 * @copyright   Copyright (c) 2012 Mage World (http://www.mageworld.com)
 
 */


/**
 * Product reports admin controller
 *
 * @category   Glace
 * @package    Glace_Dailydeal
 * @author     Magento Developer <chinhbt@asiaconnect.com.vn>
 */
//lay tu Mage_Adminhtml_Block_Report_Product
class Glace_Dailydeal_Block_Adminhtml_Dailyschedule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	
		
  public function __construct()
  {
    $this->_controller = 'adminhtml_dailyschedule';
    $this->_blockGroup = 'dailydeal';
    $this->_headerText = '<i class="fa fa-bolt fa-2x"></i>'.Mage::helper('dailydeal')->__('Manage Deals With Days');
      $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');	
    parent::__construct();
    		
    $this->_removeButton('add');
  }

}