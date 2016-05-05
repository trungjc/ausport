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
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Quickview
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Quickview_Helper_Data extends Mage_Core_Helper_Abstract {

    public function checkquickview($id) {
        if (Mage::getStoreConfig('quickview/settings/enabled')) {
            if ($this->ifPrice($id)) {
                return true;
            }
        }

        return false;
    }

    public function getquickviewText($id) {
        if (Mage::getStoreConfig('quickview/settings/enabled')) {
            if ($this->ifPrice($id) && !Mage::registry('quickview_' . $id)) {
                Mage::register('quickview_' . $id, 1);
                return Mage::getStoreConfig('quickview/settings/text');
            }
        }
        return '';
    }

    private function ifPrice($id) {
        $_product = Mage::getModel('catalog/product')->load($id);
        if ($_product->getPrice() <= 0) {
            return true;
        }
        return false;
    }

}