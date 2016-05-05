<?php

class Glace_Dailydeal_Model_Dealschedulerproduct extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('dailydeal/dealschedulerproduct');
    }

    /**
     * @return Glace_Dailydeal_Model_Dealschedulerproduct
     */
    public static function getInstance() {
        return Mage::getSingleton('dailydeal/dealschedulerproduct');
    }

    /**
     * @return Glace_Dailydeal_Model_Dealschedulerproduct
     */
    public static function getModel() {
        return Mage::getModel('dailydeal/dealschedulerproduct');
    }

    public function deleteAllByDealSchedulerId($deal_scheduler_id) {
        $collection = $this->getCollection()->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id);
        foreach ($collection as $row) {
            $row->delete();
        }
    }

    /**
     * Return array 2D product of deal scheduler
     */
    public function getProductOptionArray($deal_scheduler_id) {
        $collection = $this->getCollection()
                ->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id)
                ->setOrder('deal_position', 'ASC');

        $data = array();
        foreach ($collection as $row) {
            $data[$row->getdata('product_id')] = $row->getdata();
        }
        return $data;
    }

    /**
     * Return array 2D product of deal scheduler order by 'generate_type'
     */
    public function sortProductOptionArray($data = array(), $generate_type = Glace_Dailydeal_Model_Status::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS) {

        if ($generate_type == Glace_Dailydeal_Model_Status::DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY) {
            shuffle($data);
        } elseif ($generate_type == Glace_Dailydeal_Model_Status::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS) {
            Glace_Dailydeal_Helper_Data::reIndexArray($data);
        }
        return $data;
    }
}