<?php
class Glace_Dailydeal_Model_Source_Sortby
{
    const SORT_BY_FEATURED_ENDDATETIME = 1;
    const SORT_BY_RANDOM = 2;
    const SORT_BY_FEATURED_RANDOM = 3;
    
    static public function toOptionArray()
    {
        return array(
            self::SORT_BY_FEATURED_ENDDATETIME => Mage::helper('dailydeal')->__('Display Featured/Expiring Soonest Deals First'),
            self::SORT_BY_RANDOM => Mage::helper('dailydeal')->__('Display All Current Deals Randomly'),
            self::SORT_BY_FEATURED_RANDOM => Mage::helper('dailydeal')->__('Display Featured Deals Randomly'),
        );
    }
}