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

class Ophirah_Qquoteadv_Helper_Not2order extends Mage_Core_Helper_Data
{

    function getShowPrice($_product)
    {
        try {
            if (@class_exists('Ophirah_Not2Order_Helper_Data', true)) {
                return Mage::helper('not2order')->getShowPrice($_product);
            } else {
                // No helper, no price toggle.. always show
                return true;
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            Mage::log('Exception: ' .$e->getMessage(), null, 'n2o_exception.log', true);
            return true;
        }
    }
}