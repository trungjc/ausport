<?php
/**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 4.1
 * @since        Class available since Release 4.1
 */

class GoMage_Navigation_Model_Enterprise_Search_Observer extends Enterprise_Search_Model_Observer
{

    public function resetCurrentCatalogLayer(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('gomage_navigation');
        if (!$helper->isGomageNavigation()) {
            parent::resetCurrentCatalogLayer($observer);
        }
    }

}
