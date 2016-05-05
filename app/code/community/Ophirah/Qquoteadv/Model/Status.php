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

class Ophirah_Qquoteadv_Model_Status extends Mage_Core_Model_Abstract
{
    CONST STATUS_BEGIN = 1;
    CONST STATUS_BEGIN_ACTION_OWNER = 2;
    CONST STATUS_BEGIN_ACTION_CUSTOMER = 3;

    CONST STATUS_PROPOSAL_BEGIN = 10;
    CONST STATUS_PROPOSAL_BEGIN_ACTION_OWNER = 11;
    CONST STATUS_PROPOSAL_BEGIN_ACTION_CUSTOMER = 12;

    CONST STATUS_REQUEST = 20;
    CONST STATUS_REQUEST_EXPIRED = 21;
    CONST STATUS_REQUEST_ACTION_OWNER = 22;
    CONST STATUS_REQUEST_ACTION_CUSTOMER = 23;

    CONST STATUS_REJECTED = 30;

    CONST STATUS_CANCELED = 40;

    CONST STATUS_PROPOSAL = 50;
    CONST STATUS_PROPOSAL_EXPIRED = 51;
    CONST STATUS_PROPOSAL_SAVED = 52;
    CONST STATUS_AUTO_PROPOSAL = 53;
    CONST STATUS_CONFIRMED_ALTERNATE = 54;
    CONST STATUS_PROPOSAL_ACTION_OWNER = 56;
    CONST STATUS_PROPOSAL_ACTION_CUSTOMER = 57;

    CONST STATUS_DENIED = 60;

    CONST STATUS_CONFIRMED = 70;
    CONST STATUS_ORDERED = 71;

    static public function getOptionArray($substatus = false)
    {
        $helper = Mage::helper('qquoteadv');
        $optionArray = array(
            self::STATUS_BEGIN =>
                $helper->__('STATUS_BEGIN'),
            self::STATUS_BEGIN_ACTION_OWNER =>
                $helper->__('STATUS_BEGIN') . ' ' . $helper->__('ACTION_OWNER'),
            self::STATUS_BEGIN_ACTION_CUSTOMER =>
                $helper->__('STATUS_BEGIN') . ' ' . $helper->__('ACTION_CUSTOMER'),
            self::STATUS_PROPOSAL_BEGIN =>
                $helper->__('STATUS_PROPOSAL_BEGIN'),
            self::STATUS_REQUEST =>
                $helper->__('STATUS_REQUEST'),
            self::STATUS_REQUEST_EXPIRED =>
                $helper->__('STATUS_REQUEST_EXPIRED'),
            self::STATUS_REQUEST_ACTION_OWNER =>
                $helper->__('STATUS_REQUEST') . ' ' . $helper->__('ACTION_OWNER'),
            self::STATUS_REQUEST_ACTION_CUSTOMER =>
                $helper->__('STATUS_REQUEST') . ' ' . $helper->__('ACTION_CUSTOMER'),
            self::STATUS_PROPOSAL =>
                $helper->__('STATUS_PROPOSAL'),
            self::STATUS_PROPOSAL_EXPIRED =>
                $helper->__('STATUS_PROPOSAL_EXPIRED'),
            self::STATUS_PROPOSAL_SAVED =>
                $helper->__('STATUS_PROPOSAL_SAVED'),
            self::STATUS_AUTO_PROPOSAL =>
                $helper->__('STATUS_AUTO_PROPOSAL'),
            self::STATUS_PROPOSAL_ACTION_OWNER =>
                $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_OWNER'),
            self::STATUS_PROPOSAL_ACTION_CUSTOMER =>
                $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_CUSTOMER'),
            self::STATUS_CANCELED =>
                $helper->__('STATUS_CANCELED'),
            self::STATUS_DENIED =>
                $helper->__('STATUS_DENIED'),
            self::STATUS_CONFIRMED =>
                $helper->__('STATUS_CONFIRMED'),
            self::STATUS_CONFIRMED_ALTERNATE =>
                $helper->__('STATUS_CONFIRMED'),
            self::STATUS_ORDERED =>
                $helper->__('STATUS_ORDERED'),
        );

        // Add Substatuses
        if (Mage::getModel('qquoteadv/substatus')->substatuses() && $substatus === true) {
            $optionArray = Mage::getModel('qquoteadv/substatus')
                ->getSubOptionArray($optionArray, $substatus);
            return $optionArray;
        }

        return $optionArray;

    }

    static public function getGridOptionArray($substatus = false)
    {
        $helper = Mage::helper('qquoteadv');
        $gridOptionArray = array(
            self::STATUS_REQUEST =>
                $helper->__('STATUS_REQUEST'),
            self::STATUS_REQUEST_ACTION_OWNER =>
                $helper->__('STATUS_REQUEST'). ' ' . $helper->__('ACTION_OWNER'),
            self::STATUS_REQUEST_ACTION_CUSTOMER =>
                $helper->__('STATUS_REQUEST'). ' ' . $helper->__('ACTION_CUSTOMER'),
            self::STATUS_PROPOSAL_BEGIN =>
                $helper->__('STATUS_PROPOSAL_BEGIN'),
            self::STATUS_REQUEST_EXPIRED =>
                $helper->__('STATUS_REQUEST_EXPIRED'),
            self::STATUS_PROPOSAL =>
                $helper->__('STATUS_PROPOSAL'),
            self::STATUS_PROPOSAL_EXPIRED =>
                $helper->__('STATUS_PROPOSAL_EXPIRED'),
            self::STATUS_PROPOSAL_SAVED =>
                $helper->__('STATUS_PROPOSAL_SAVED'),
            self::STATUS_PROPOSAL_ACTION_OWNER =>
                $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_OWNER'),
            self::STATUS_PROPOSAL_ACTION_CUSTOMER =>
                $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_CUSTOMER'),
            self::STATUS_AUTO_PROPOSAL =>
                $helper->__('STATUS_AUTO_PROPOSAL'),
            self::STATUS_CANCELED =>
                $helper->__('STATUS_CANCELED'),
            self::STATUS_DENIED =>
                $helper->__('STATUS_DENIED'),
            self::STATUS_CONFIRMED =>
                $helper->__('STATUS_CONFIRMED'),
            self::STATUS_CONFIRMED_ALTERNATE =>
                $helper->__('STATUS_CONFIRMED'),
            self::STATUS_ORDERED =>
                $helper->__('STATUS_ORDERED'),
        );

        // Check for substatuses
        if (Mage::getModel('qquoteadv/substatus')->substatuses() && $substatus === true) {
            $gridOptionArray = Mage::getModel('qquoteadv/substatus')
                ->getSubOptionArray($gridOptionArray, $substatus);
        }

        return $gridOptionArray;


    }

    static public function getChangeOptionArray($substatus = false)
    {
        $helper = Mage::helper('qquoteadv');
        $changeOptionArray = array(
            array(
                'value' => self::STATUS_PROPOSAL_BEGIN,
                'label' => $helper->__('STATUS_PROPOSAL_BEGIN')),
            array(
                'value' => self::STATUS_BEGIN_ACTION_OWNER,
                'label' => $helper->__('STATUS_BEGIN'). $helper->__('ACTION_OWNER')),
            array(
                'value' => self::STATUS_BEGIN_ACTION_CUSTOMER,
                'label' => $helper->__('STATUS_BEGIN'). ' ' . $helper->__('ACTION_CUSTOMER')),
            array(
                'value' => self::STATUS_REQUEST,
                'label' => $helper->__('STATUS_REQUEST')),
            array(
                'value' => self::STATUS_REQUEST_EXPIRED,
                'label' => $helper->__('STATUS_REQUEST_EXPIRED')),
            array(
                'value' => self::STATUS_REQUEST_ACTION_OWNER,
                'label' => $helper->__('STATUS_REQUEST'). ' ' . $helper->__('ACTION_OWNER')),
            array(
                'value' => self::STATUS_REQUEST_ACTION_CUSTOMER,
                'label' => $helper->__('STATUS_REQUEST'). ' ' . $helper->__('ACTION_CUSTOMER')),
            array(
                'value' => self::STATUS_PROPOSAL,
                'label' => $helper->__('STATUS_PROPOSAL')),
            array(
                'value' => self::STATUS_PROPOSAL_EXPIRED,
                'label' => $helper->__('STATUS_PROPOSAL_EXPIRED')),
            array(
                'value' => self::STATUS_PROPOSAL_SAVED,
                'label' => $helper->__('STATUS_PROPOSAL_SAVED')),
            array(
                'value' => self::STATUS_PROPOSAL_ACTION_OWNER,
                'label' => $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_OWNER')),
            array(
                'value' => self::STATUS_PROPOSAL_ACTION_CUSTOMER,
                'label' => $helper->__('STATUS_PROPOSAL'). ' ' . $helper->__('ACTION_CUSTOMER')),
            /*
            array(
                'value' => self::STATUS_AUTO_PROPOSAL,
                'label' => $helper->__('STATUS_AUTO_PROPOSAL')),
            */
            array(
                'value' => self::STATUS_CANCELED,
                'label' => $helper->__('STATUS_CANCELED')),
            array(
                'value' => self::STATUS_DENIED,
                'label' => $helper->__('STATUS_DENIED')),
            array(
                'value' => self::STATUS_CONFIRMED,
                'label' => $helper->__('STATUS_CONFIRMED')),
            array(
                'value' => self::STATUS_CONFIRMED_ALTERNATE,
                'label' => $helper->__('STATUS_CONFIRMED')),
            array(
                'value' => self::STATUS_ORDERED,
                'label' => $helper->__('STATUS_ORDERED')),
        );

        // Check for substatuses
        if (Mage::getModel('qquoteadv/substatus')->substatuses() && $substatus === true) {
            $changeOptionArray = Mage::getModel('qquoteadv/substatus')
                ->getChangeSubOptionArray($changeOptionArray, $substatus);
        }

        return $changeOptionArray;
    }

    static public function statusAllowed()
    {

        $statusAllowed = array(self::STATUS_BEGIN,
            self::STATUS_PROPOSAL_BEGIN,
            self::STATUS_REQUEST,
            self::STATUS_PROPOSAL,
            self::STATUS_PROPOSAL_SAVED,
            self::STATUS_AUTO_PROPOSAL
        );

        return $statusAllowed;
    }

    /**
     * Statuses that needs to be filtered
     * for setting quote to expired.
     *
     * @return array
     */
    static public function statusExpire()
    {

        $statusExpire = array(self::STATUS_PROPOSAL,
            self::STATUS_PROPOSAL_SAVED,
            self::STATUS_AUTO_PROPOSAL
        );

        return $statusExpire;
    }

    /**
     * Create status update object for
     * Ophirah_Qquoteadv_Adminhtml_QquoteadvController::massStatusAction()
     *
     * @param string $status
     * @return \Varien_Object
     */
    public function getStatus($status)
    {
        // Check for substatuses
        if (Mage::getModel('qquoteadv/substatus')->substatuses()) {
            $return = Mage::getModel('qquoteadv/substatus')->getStatus($status);
        } else {
            $return = new Varien_Object();
            $return->setStatus((int)$status);
        }

        return $return;
    }

    /**
     * @return int
     * Mage::getModel('qquoteadv/status')->getStatusConfirmed();
     */
    public function getStatusConfirmed() {

        if(Mage::getStoreConfig('qquoteadv_advanced_settings/checkout/confirmed_alternate')){
            return self::STATUS_CONFIRMED_ALTERNATE;
        }
        return self::STATUS_CONFIRMED;
    }
}
