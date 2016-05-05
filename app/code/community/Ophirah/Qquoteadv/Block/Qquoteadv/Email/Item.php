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

class Ophirah_Qquoteadv_Block_Qquoteadv_Email_Item extends Mage_Checkout_Block_Cart_Abstract
{
    public $autoproposal = 0;

    /**
     * Get Product information from qquote_request_item table
     * @return object
     */
    public function getRequestedProductData($id, $quoteId)
    {
        $prices = array();
        $aQty = array();

        $quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($quoteId);
        $collection = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
            ->addFieldToFilter('quoteadv_product_id', $id);
        $collection->getSelect()->order('request_qty asc');

        $n = count($collection);
        if ($n > 0) {
            foreach ($collection as $requested_item) {
                $aQty[] = $requested_item->getRequestQty();
                $prices[] = $requested_item->getOwnerCurPrice();
            }
        }

        return $return = array(
            'ownerPrices' => $prices,
            'aQty' => $aQty
        );
    }

    /**
     * Set value from template autoproposal
     *
     * @param $val
     * @return $this
     */
    public function setAutoproposal($val)
    {
        $this->autoproposal = $val;
        return $this;
    }

    /**
     * AutoProposal checker
     *
     * @return int
     */
    public function isSetAutoProposal()
    {
        return $this->autoproposal;
    }
}
