<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /***************************************
 *         DISCLAIMER   *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Quickview
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */?>

<?php

class Belvg_Quickview_AjaxController extends Mage_Core_Controller_Front_Action
{ 

	public function popupAction() { 		
		if (Mage::getStoreConfig('quickview/settings/enabled',Mage::app()->getStore()) == 0) echo '';
		if (Mage::registry('product_popup_id'))
			Mage::unregister('product_popup_id');
		Mage::register('product_popup_id',$this->getRequest()->getPost('pro_id'));
	//	$this->loadLayout();
	//	$this->getLayout()->getBlock('root')->setTemplate('page/empty.phtml');	
	//	$this->getLayout()->removeOutputBlock('head');
		$qvBlock = $this->getLayout()->createBlock('core/template')
			->setTemplate('quickview/popup.phtml');
		echo $qvBlock->toHtml();
		//$this->getLayout()->getBlock('content')->append($qvBlock);
		//$this->renderLayout();
	}

	
}
