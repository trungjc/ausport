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

class Ophirah_Qquoteadv_Block_Adminhtml_Email_Items extends Mage_Sales_Block_Items_Abstract //Mage_Core_Block_Template
{

    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('id');
        if (!$quoteId) {
            $quoteObj = $this->getData('quote');
            if (is_object($quoteObj)) {
                $quoteId = $quoteObj->getQuoteId();
            }
        }

        if ($quoteId) {
            $quoteData = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
                ->addFieldToFilter('quote_id', $quoteId);

            foreach ($quoteData as $key => $quote) {
                $this->setQuoteId($quoteId);
                return $quote;
            }
        }
        return null;
    }

    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getAllItems()
    {
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteProduct($this->getQuoteId());

        return $collection;
    }
}