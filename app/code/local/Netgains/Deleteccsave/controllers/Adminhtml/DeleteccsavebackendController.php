<?php
class Netgains_Deleteccsave_Adminhtml_DeleteccsavebackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Deleteorder"));
	   $this->renderLayout();
    }
    public function DeletecreditcardAction()
    {
		$i = 0;
		$orderIds = $this->getRequest()->getParam('order_ids');
		$resource = Mage::getSingleton('core/resource');
		$readConnection 	=	$resource->getConnection('core/resource');
		try
		{
			foreach ($orderIds as $values)
			{
				$transactions=Mage::getModel('sales/order')->load($values);
				if ($transactions->status == 'complete' || $transactions->status == 'canceled')
				{
					$updatefirsttable = "UPDATE `sales_flat_order_payment` SET `cc_number_enc` = NULL,`cc_exp_month`=NULL,`cc_exp_year`=NULL,`cc_type`=NULL WHERE `entity_id` =$transactions->entity_id";
					$updatesecondtable = "UPDATE `sales_flat_quote_payment` SET `cc_number_enc` = NULL,`cc_exp_month`=NULL,`cc_exp_year`=NULL,`cc_type`=NULL WHERE `quote_id` =$transactions->quote_id";
					$readConnection->query($updatefirsttable);
					$readConnection->query($updatesecondtable);
					$i++;
					Mage::getSingleton('core/session')->addSuccess('CC DELETED SUCCESSFULLY For'.' '.$i.' '.'Orders');
				}else
				{
					Mage::getSingleton('core/session')->addError('You cannot delete CC information because order is not in complete or canceled state');
				}
				
			}
			
		}
		catch(Exception $e)
		{
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		
		$this->_redirect('adminhtml/sales_order');
		
	}
    
}
