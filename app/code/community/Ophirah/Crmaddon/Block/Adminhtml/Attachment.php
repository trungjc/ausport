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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

class Ophirah_Crmaddon_Block_Adminhtml_Attachment extends Mage_Checkout_Block_Cart_Abstract
{
    public function _construct()
    {
        return parent::_construct();
    }

    /**
     * Returns an array of attachments
     * Per attachment the following fields:
     *      String - text
     *      DateTime - mod_date
     *      String - permissions
     *      String - owner
     *      Int - size
     *      Boolean - leaf
     *      Boolean - is_image
     *      String - filetype
     * @return array
     */
    public function getAttachments(){
        $attachments = array();
        if($attachmentModel = $this->getAttachmentModel()){
            $attachments = $attachmentModel->getAttachments();
        }
        return $attachments;
    }

    /**
     * Checks if the key is set to the array
     * @param $array
     * @param $field
     * @return string
     */
    public function getField($array, $field){
        if(is_array($array) && array_key_exists($field, $array)){
            return $array[$field];
        }else{
            return '';
        }
    }

    /**
     * Function that cuts down a string based on a given number of chars
     *
     * @param $text
     * @param $chars
     * @return string
     */
    public function getSmallText($text, $chars){
        if(strlen($text) > $chars){
            $text = $text." ";
            $text = substr($text,0,$chars);
            $text = $text."...";
        }
        return $text;
    }

    /**
     * Returns a path of an attachment
     * @return String
     */
    public function getItemLink($attachmentName){
        return $this->getAttachmentModel()->getAttachmentPath($attachmentName);
    }

    /**
     * @return Mage_Core_Model_Abstract | NULL
     */
    public function getCrmMessageModel(){
        if($crmMessage = $this->getParentBlock()->getActiveCrmMessage()) {
            if(is_array($crmMessage) && array_key_exists('message_id', $crmMessage)){
                return Mage::getModel('crmaddon/crmaddonmessages')->load($crmMessage['message_id']);
            }
        }
    }

    /**
     * @return Ophirah_Crmaddon_Model_Attachment | NULL
     */
    public function getAttachmentModel(){
        if($crmMessageModel = $this->getCrmMessageModel()) {
            return Mage::getModel('crmaddon/attachment')->ini($crmMessageModel);
        }
    }

}
