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

/**
 * Currency controller
 */
require_once Mage::getModuleDir('controllers', 'Mage_Directory') . DS . 'CurrencyController.php';

class Ophirah_Qquoteadv_Directory_CurrencyController extends Mage_Directory_CurrencyController
{
    public function switchAction()
    {
        if ($curency = (string)$this->getRequest()->getParam('currency')) {
            Mage::dispatchEvent('ophirah_qquoteadv_currencySwitch_before', array($curency));

            if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {
                $message = Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
                Mage::getSingleton('checkout/session')->addError($message);

                $link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
                $message = Mage::helper('qquoteadv')->__("To change your currency <a href='%s'>log out</a> from Quote confirmation mode.", $link);
                Mage::getSingleton('checkout/session')->addNotice($message);
            } else {
                Mage::app()->getStore()->setCurrentCurrencyCode($curency);
            }

            Mage::dispatchEvent('ophirah_qquoteadv_currencySwitch_after', array($curency));
        }
        $this->_redirectReferer(Mage::getBaseUrl());
    }
}
