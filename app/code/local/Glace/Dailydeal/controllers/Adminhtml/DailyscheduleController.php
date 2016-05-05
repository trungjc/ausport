<?php

class Glace_Dailydeal_Adminhtml_DailyscheduleController extends Mage_Adminhtml_Controller_Action
{

    public function _initAction()
    {
        Mage::getSingleton('adminhtml/session')->setFlag('dailyschedule');
        $this->loadLayout();
        return $this;
    }

    public function daysAction()
    {
        $this->_title(Mage::helper('dailydeal')->__('Daily Deals Ordered'));
        $this->_initAction();
        $this->_setActiveMenu('');
        $this->_addBreadcrumb(Mage::helper('dailydeal')->__('Daily Deals Ordered'), Mage::helper('dailydeal')->__('Daily Deals Ordered'));
        $this->renderLayout();
    }

}