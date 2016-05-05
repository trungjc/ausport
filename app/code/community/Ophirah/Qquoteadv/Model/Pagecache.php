<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

class Ophirah_Qquoteadv_Model_Pagecache
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function registerQuoteChange(Varien_Event_Observer $observer)
    {
        //Magento EE only:
        if(Mage::helper('core')->isModuleEnabled('Enterprise_PageCache')){
            $pageCacheObserver = Mage::getModel('enterprise_pagecache/observer');

            $observer->getEvent()->setQuote(Mage::getSingleton('checkout/session')->getQuote());
            $pageCacheObserver->registerQuoteChange($observer);

            $observer->getEvent()->setTags(Mage_Core_Model_App::CACHE_TAG);
            if($this->_checkVersion()){
                $pageCacheObserver->cleanCacheByTags($observer);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function processPreDispatch(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();

        // Check to see if $action is a Product controller
        if ($action instanceof Ophirah_Qquoteadv_IndexController) {
            $cache = Mage::app()->getCacheInstance();

            // Tell Magento to 'ban' the use of FPC for this request
            $cache->banUse('full_page');
        }
    }

    /**
     * Checks if the version is 1.13.0.0 or higher (since the release of some FPC functions)
     */
    protected function _checkVersion(){
        if(method_exists('Mage', 'getEdition')){
            $edition = Mage::getEdition();
            switch ($edition) {
                case "Enterprise":
                    return version_compare(Mage::getVersion(), '1.13.0.0') < 0 ? false : true;
                default:
                    //no enterprice
                    return false;
            }
        } else {
            //version is below v1.7.0.0/v1.12.0.0
            return false;
        }
    }
}
