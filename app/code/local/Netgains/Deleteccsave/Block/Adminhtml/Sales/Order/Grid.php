<?php
class Netgains_Deleteccsave_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	
	protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
         
        // Append new mass action option
        $this->getMassactionBlock()->addItem(
            'newmodule',
            array('label' => $this->__('Netgains-Delete Credit Card Information'),
                  'url'   => $this->getUrl('deleteccsave/adminhtml_deleteccsavebackend/Deletecreditcard') //this should be the url where there will be mass operation
            )
        );
    }
}
			
