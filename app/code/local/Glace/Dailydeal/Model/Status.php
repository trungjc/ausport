<?php

class Glace_Dailydeal_Model_Status extends Varien_Object
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    /**
     * Status
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED => Mage::helper('adminhtml')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('adminhtml')->__('Disabled'),
        );
    }

    const STATUS_PRODUCT_ENABLED = 1;
    const STATUS_PRODUCT_DISABLED = 0;
    const STATUS_PRODUCT_DONE = 2;

    /**
     * Disable product after deal ends
     */
    static public function getProductOptionArray()
    {
        return array(
            self::STATUS_PRODUCT_ENABLED => Mage::helper('adminhtml')->__('Yes'),
            self::STATUS_PRODUCT_DISABLED => Mage::helper('adminhtml')->__('No'),
        );
    }

    const STATUS_FEATURED_ENABLED = 1;
    const STATUS_FEATURED_DISABLED = 2;

    /**
     * Featured
     */
    static public function getFeaturedOptionArray()
    {
        return array(
            self::STATUS_FEATURED_ENABLED => Mage::helper('adminhtml')->__('Yes'),
            self::STATUS_FEATURED_DISABLED => Mage::helper('adminhtml')->__('No'),
        );
    }
    
    const STATUS_TIME_QUEUED = 0;
    const STATUS_TIME_RUNNING = 1;
    const STATUS_TIME_DISABLED = 2;
    const STATUS_TIME_ENDED = 3;
    static public function getStatusTimeOptionArray()
    {
        return array(
            self::STATUS_TIME_QUEUED => Mage::helper('dailydeal')->__('Queued'),
            self::STATUS_TIME_RUNNING => Mage::helper('dailydeal')->__('Running'),
            self::STATUS_TIME_ENDED => Mage::helper('dailydeal')->__('Ended'),
            self::STATUS_TIME_DISABLED => Mage::helper('adminhtml')->__('Disabled'),
        );
    }
    
    const STATUS_EXPIRE_TRUE = 1;
    const STATUS_EXPIRE_FALSE = 0;
    
    
    const DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY = 0;
    const DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS = 1;

    /**
     * Generation Type
     */
    static public function getDealSchedulerGenerateTypeOptionArray()
    {
        return array(
            self::DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY => Mage::helper('dailydeal')->__('At Random'),
            self::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS => Mage::helper('dailydeal')->__('Sequentially'),
        );
    }
    
    const DEAL_SCHEDULER_GENERATE_LIMIT_AMOUNT = 200;
    
    const PRODUCT_HAVE_DEAL_ACTIVE = 1;
    const PRODUCT_HAVE_DEAL_ENDED = 3;
    
    /**
     * Mail
     */
    const SEND_MAIL_ADMIN_NOTIFICATION_DISABLE = 0;
    const SEND_MAIL_ADMIN_NOTIFICATION_ENABLE = 1;
}