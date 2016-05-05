<?php

class Glace_Dailydeal_Model_Mysql4_Dealscheduler extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('dailydeal/dealscheduler', 'deal_scheduler_id');
    }

}