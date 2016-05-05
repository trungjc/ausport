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

class Ophirah_Qquoteadv_Model_Entity_Increment_Numeric extends Mage_Eav_Model_Entity_Increment_Numeric
{

    CONST PARAM_START_NUMBER = 'qquoteadv_quote_configuration/quote_number_format/startnumber';
    CONST PARAM_PREFIX = 'qquoteadv_quote_configuration/quote_number_format/prefix';
    CONST PARAM_INCREMENT = 'qquoteadv_quote_configuration/quote_number_format/increment';
    CONST PARAM_PAD_LENGTH = 'qquoteadv_quote_configuration/quote_number_format/pad_length';

    CONST QUOTE_TYPE_ID = 888;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $aEntityTypes = array(self::QUOTE_TYPE_ID => 'qquoteadv');
        $this->setData('entity_types', $aEntityTypes);
    }

    /**
     * Function that generates a new quote increment id
     * 
     * @param null $storeId
     * @return string
     */
    protected function _generateNextId($storeId = null)
    {
        $aEntityTypes = $this->getData('entity_types');
        $entTypeId = self::QUOTE_TYPE_ID;

        if (isset($aEntityTypes[$entTypeId])) {
            $entityType = $aEntityTypes[$entTypeId];

            $rowStartNumber = Mage::getStoreConfig(self::PARAM_START_NUMBER, $storeId);
            $rowPrefix = Mage::getStoreConfig(self::PARAM_PREFIX, $storeId);
            $rowIncrement = Mage::getStoreConfig(self::PARAM_INCREMENT, $storeId);
            $rowPadLenght = Mage::getStoreConfig(self::PARAM_PAD_LENGTH, $storeId);

            $this->setData(array('pad_length' => $rowPadLenght, 'increment' => $rowIncrement, 'startnumber' => $rowStartNumber, 'prefix' => $rowPrefix));
            $nextNum = $rowStartNumber + $rowIncrement;

            //#update core_config_data table with new quote numeration value
            if ($nextNum) {

                if(is_integer($storeId)){
                    Mage::app()->getConfig()->saveConfig(self::PARAM_START_NUMBER, $nextNum, 'stores', $storeId)->reinit();
                }else{
                    //Uncomment if next number should be stored per store
                    Mage::app()->getConfig()->saveConfig(self::PARAM_START_NUMBER, $nextNum, 'stores', Mage::app()->getStore()->getId())->reinit();
                    // Mage::app()->getConfig()->saveConfig(self::PARAM_START_NUMBER, $nextNum)->reinit();
                }
            }
            return $this->format($nextNum);

        } else {
            return parent::getNextId();
        }
    }

    /**
     * Function to get the next increment id
     * 
     * @param null $storeId
     * @return string
     */
    public function getNextId($storeId = null)
    {
        if (Mage::helper('qquoteadv')->getTotalQty() > 0) {
            return $this->_generateNextId($storeId);
        } else {
            $last = $this->getLastId();

            if (strpos($last, $this->getPrefix()) === 0) {
                $last = (int)substr($last, strlen($this->getPrefix()));
            } else {
                $last = (int)$last;
            }

            $next = $last + 1;

            return $this->format($next);
        }
    }

    /**
     * Get the increment id length set in the configuration page
     *
     * @return int
     */
    public function getPadLength()
    {
        $padLength = $this->getData('pad_length');
        if (empty($padLength)) {
            $padLength = 0;
        }

        return $padLength;
    }

    /**
     * Format a increment id with the path length and the prefix
     *
     * @param $id
     * @return string
     */
    public function format($id)
    {
        $result = $this->getPrefix();
        $result .= str_pad((string)$id, $this->getPadLength(), $this->getPadChar(), STR_PAD_LEFT);

        return $result;
    }

}