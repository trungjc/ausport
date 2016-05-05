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

class Ophirah_Qquoteadv_Model_Quotetrail extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/quotetrail');
    }

    /**
     * Function for adding a message to the quote trail
     *
     * @param $message
     * @param int $quoteId
     */
    public function addMessage($message, $quoteId = 0){
        $adminSession = Mage::getSingleton('admin/session');
        if($user = $adminSession->getUser()){
            //$message .= ' by <strong>'.$user->getFirstname().' '.$user->getLastname().'</strong>';
            $this->setUserId($user->getUserId());
        }

        $this->setQuoteId($quoteId);
        $this->setMessage($message);
        $this->setCreatedAt(NOW());
        $this->setUpdatedAt(NOW());
        $this->save();
    }
}
