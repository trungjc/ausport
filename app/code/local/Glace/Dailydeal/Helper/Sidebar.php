<?php

class Glace_Dailydeal_Helper_Sidebar extends Mage_Core_Helper_Abstract
{

    public function displayTodaydealLeft()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/sidebardeal') == 1)
            return "glace_dailydeal/sidebar/todaydeal.phtml";
    }

    public function displayActivedealLeft()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/sidebaractive') == 1)
            return "glace_dailydeal/sidebar/activedeal.phtml";
    }

    public function displayCalendarLeft()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/calendar') == 1) {
            return "glace_dailydeal/sidebar/calendar.phtml";
        }
    }

    public function displayTodaydealRight()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/sidebardeal', Mage::app()->getStore()->getStoreId()) == 2)
            return "glace_dailydeal/sidebar/todaydeal.phtml";
    }

    public function displayActivedealRight()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/sidebaractive', Mage::app()->getStore()->getStoreId()) == 2)
            return "glace_dailydeal/sidebar/activedeal.phtml";
    }

    public function displayCalendarRight()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        if (Mage::getStoreConfig('dailydeal/general/calendar', Mage::app()->getStore()->getStoreId()) == 2)
            return "glace_dailydeal/sidebar/calendar.phtml";
    }

    /**
     * Simple Products
     */
    public function getTemplateViewProduct()
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }
        return "glace_dailydeal/catalog/product/view/type/default.phtml";
    }
    
}
