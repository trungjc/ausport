<?php

class Glace_Dailydeal_Model_Mysql4_Dealschedulerproduct_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init("dailydeal/dealschedulerproduct");
    }

}