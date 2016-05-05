<?php

class Glace_Dailydeal_Model_Mysql4_Dealscheduler_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init("dailydeal/dealscheduler");
    }

}