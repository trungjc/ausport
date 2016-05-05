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

class Ophirah_Qquoteadv_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{

    protected function _toHtml()
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $params = Mage::app()->getRequest()->getParams();

        if (!(isset($params['section']) && $params['section'] == 'qquoteadv' && 'system_config' == $controllerName))
            return '';

        if (Mage::helper('qquoteadv')->isEnabled() && !Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl'))
            return parent::_toHtml();
        else
            return '';
    }
}