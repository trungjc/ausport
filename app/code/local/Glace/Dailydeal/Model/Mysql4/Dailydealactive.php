<?php

class Glace_Dailydeal_Model_Mysql4_Dailydealactive extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the dailydeal_id refers to the key field in your database table.
        $this->_init('dailydeal/dailydealactive', 'id');
    }
}