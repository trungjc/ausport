<?php

class Glace_Dailydeal_Model_Dealscheduler extends Mage_Core_Model_Abstract
{

    const PATTERN_DEAL_PRICE_1 = "/^[0-9]+-[0-9]+$/i";     // 10 - 20
    const PATTERN_DEAL_PRICE_2 = "/^[0-9]+%-[0-9]+%$/i";   // 10% - 20%
    const PATTERN_DEAL_PRICE_3 = "/^([0-9]+(\.[0-9]+)?%?)(,[0-9]+(\.[0-9]+)?%?)*$/i";  // 9.99, 15, 5%, 7.5%
    const PATTERN_DEAL_QTY_BLANK = '/^$/i';                // blank
    const PATTERN_DEAL_QTY_1 = "/^[0-9]+-[0-9]+$/i";       // 10 - 20
    const PATTERN_DEAL_QTY_2 = "/^([0-9]+)(,[0-9]+)*$/i";  // 10, 15, 20

    public $product_exist = array();
    public $count = 0;

    public function _construct()
    {
        parent::_construct();
        $this->_init('dailydeal/dealscheduler');
    }

    /**
     * @return Glace_Dailydeal_Model_Dealscheduler
     */
    public static function getInstance()
    {
        return Mage::getSingleton('dailydeal/dealscheduler');
    }

    /**
     * @return Glace_Dailydeal_Model_Dealscheduler
     */
    public static function getModel()
    {
        return Mage::getModel('dailydeal/dealscheduler');
    }

    public function save()
    {
        $this->validate();
        $this->setDataDefault();
        parent::save();
    }

    protected function validate()
    {
        // Check start_date_time < end_date_time
        $start_timestamp = (int) strtotime($this->getData('start_date_time'));
        $end_timestamp = (int) strtotime($this->getData('end_date_time'));

        if ($start_timestamp > $end_timestamp) {
            throw new Exception(Mage::helper('dailydeal')->__("'To Date' must be equal or more than 'From Date'"));
        }

        // Validate number thread >= number product id
        $number_thread = (int) $this->getData('number_deal');
        $number_product_id = count($this->getData('radioproduct'));
        if (empty($number_product_id)) {
            $model_product_scheduler = Glace_Dailydeal_Model_Dealschedulerproduct::getModel();
            $products = $model_product_scheduler->getProductOptionArray($this->getId());
            $number_product_id = count($products);
        }
        if ($number_thread > $number_product_id) {
            throw new Exception(Mage::helper('dailydeal')->__("Number of daily deals at the same time can not be greater than number of selected products"));
        }
    }

    protected function setDataDefault()
    {
        $number_deal = $this->getData('number_deal');
        if (empty($number_deal)) {
            $this->setData('number_deal', 1);
        }
        $number_day = $this->getData('number_day');
        if (empty($number_day)) {
            $this->setData('number_day', 1);
        }
    }

    /**
     * Start time of deal ( process follow 'end_date_time' deal )
     */
    public function getThreadStartDateTime($index, $thread_start_date_time)
    {
        $data = array();

        if (empty($thread_start_date_time)) {
            $data[$index] = $this->getData('start_date_time');
        } else {
            $data[$index] = $thread_start_date_time;
        }

        $now_time = strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $data_time = strtotime($data[$index]);

        if ($now_time >= $data_time) {
            $data[$index] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        } elseif ($data_time > $now_time) {
            $data[$index] = Glace_Dailydeal_Helper_Toolasiaconnect::increaseTime($data[$index], 3, 1, 'Y-m-d H:i:s');
        }

        return $data[$index];
    }

    /**
     * End time of deal
     */
    public function getLimitEndDateTime($to)
    {

        $end_time = strtotime($this->getData('end_date_time'));
        $to_time = strtotime($to);

        if ($to_time > $end_time) {
            $data_time = $this->getData('end_date_time');
        } else {
            $data_time = $to;
        }

        return $data_time;
    }

    /**
     * set start_date_time for next deal
     */
    public function setLimitStartDateTime($end_date_time, $index)
    {
        $data = array();

        if ($this->getData('limit_start_date_time') != null) {
            $temp = $this->getData('limit_start_date_time');
            $data = explode(",", $temp);
        }

        for ($i = 0; $i <= $index; $i++) {
            if ($i == $index) {
                $data[$i] = $end_date_time;
            } else {
                $data[$i] = $data[$i];
            }
        }

        $string = implode(',', $data);
        $this->setData('limit_start_date_time', $string);
        return $this;
    }

    public function generalDeal($product, $index, $thread_start_date_time)
    {
        try {
            $model_product = Mage::getSingleton('catalog/product')->load($product['product_id']);
            $model_deal = Glace_Dailydeal_Model_Dailydeal::getInstance();

            $data_default = array();
            $data_default['product_id'] = $product['product_id'];
            $data_default['product_sku'] = $model_product->getData('sku');
            $data_default['product_price'] = $model_product->getData('price');
            $data_default['cur_product'] = $model_product->getData('name');
            $data_default['deal_scheduler_id'] = $this->getId();
            $data_default['store_view'] = array('0' => 0);
            $data_default['description'] = '';
            $data_default['sold_qty'] = '';
            $data_default['limit_customer'] = 0;
            $data_default['featured'] = Glace_Dailydeal_Model_Status::STATUS_FEATURED_DISABLED;
            $data_default['disable_product_after_finish'] = Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DISABLED;
            $data_default['status'] = Glace_Dailydeal_Model_Status::STATUS_ENABLED;
            $data_default['thread'] = $index;

            if (($product['deal_price']) != '')
                $data_default['dailydeal_price'] = $product['deal_price'];
            else
                $data_default['dailydeal_price'] = $this->getDealPrice($model_product);

            if (($product['deal_qty']) != '')
                $data_default['deal_qty'] = $product['deal_qty'];
            else
                $data_default['deal_qty'] = $this->getDealQty($model_product);

            if (($product['deal_time']) != '')
                $data_default['deal_time'] = $product['deal_time'];
            else
                $data_default['deal_time'] = $this->getDealTime();

            $start_date_time = $this->getThreadStartDateTime($index, $thread_start_date_time);
            $end_date_time = Glace_Dailydeal_Helper_Toolasiaconnect::increaseTime($start_date_time, 1, $data_default['deal_time'], 'Y-m-d H:i:s');
            $end_date_time = $this->getLimitEndDateTime($end_date_time);

            $data_default['start_date_time'] = $start_date_time;
            $data_default['end_date_time'] = $end_date_time;
            
            $model_deal->setData($data_default);
            
            $model_deal->save();
            
            
            $success = true;
        } catch (Exception $exc) {
            $success = false;
        }

        return array($success, $end_date_time);
    }

    /**
     * Get status active of this (deal scheduler). 
     * @return int
     */
    public function getStatusTime()
    {
        $model_deal = $this;
        $timestamp = Mage::getModel('core/date')->timestamp(time());
        $start_date_time = strtotime($model_deal->getData('start_date_time'));
        $end_date_time = strtotime($model_deal->getData('end_date_time'));

        // 1 DISABLE
        if ($model_deal->getData('status') == Glace_Dailydeal_Model_Status::STATUS_DISABLED) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED;
        }

        // 2 ENABLE, RUNNING
        elseif ($start_date_time <= $timestamp AND $timestamp <= $end_date_time) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING;
        }

        // 3 ENDED
        elseif ($end_date_time < $timestamp) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED;
        }

        // 4 QUEUED
        if ($timestamp < $start_date_time) {
            return Glace_Dailydeal_Model_Status::STATUS_TIME_QUEUED;
        }

        // Default 
        return Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED;
    }

    public function isValidDealPrice($value)
    {
        $result = false;
        $value = str_replace(' ', '', $value);

        $patterns[] = self::PATTERN_DEAL_PRICE_1;
        $patterns[] = self::PATTERN_DEAL_PRICE_2;
        $patterns[] = self::PATTERN_DEAL_PRICE_3;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function isValidDealQty($value)
    {
        $result = false;
        $value = str_replace(' ', '', $value);

        $patterns[] = self::PATTERN_DEAL_QTY_BLANK;
        $patterns[] = self::PATTERN_DEAL_QTY_1;
        $patterns[] = self::PATTERN_DEAL_QTY_2;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Process deal_price follow pattern
     */
    public function getDealPrice($model_product)
    {
        $result = 0;
        $value = str_replace(' ', '', $this->getData('deal_price'));
        $price = $model_product->getData('price');
        
        if (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_PRICE_1, $value)) {
            $temp = explode('-', $value);
            $price_down = rand($temp[0], $temp[1]);
            $result = $price - $price_down;
        } elseif (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_PRICE_2, $value)) {
            $value = str_replace('%', '', $value);
            $temp = explode('-', $value);
            $percent = rand($temp[0], $temp[1]);
            $price_down = ($price * $percent) / 100;
            $result = $price - $price_down;
        } elseif (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_PRICE_3, $value)) {
            $temp = explode(',', $value);
            $key_random = array_rand($temp);
            if (preg_match('/%/', $temp[$key_random])) {
                $percent = str_replace('%', '', $temp[$key_random]);
                $price_down = ($price * $percent) / 100;
                $result = $price - $price_down;
            } else {
                $price_down = $temp[$key_random];
                $result = $price - $price_down;
            }
        }

        return $result;
    }

    /**
     * Process deal_qty follow pattern
     */
    public function getDealQty($model_product)
    {
        $result = 1;
        $value = str_replace(' ', '', $this->getData('deal_qty'));
        if (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_QTY_BLANK, $value)) {
            $result = 0;
        }
        if (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_QTY_1, $value)) {
            $temp = explode('-', $value);
            $result = rand($temp[0], $temp[1]);
        }
        if (Glace_Dailydeal_Helper_Toolasiaconnect::isValidPattern(self::PATTERN_DEAL_QTY_2, $value)) {
            $temp = explode(',', $value);
            $key_random = array_rand($temp);
            $result = $temp[$key_random];
        }

        $model_stock_item = Mage::getSingleton('cataloginventory/stock_item')->loadByProduct($model_product);
        if ($result > $model_stock_item->getData('qty')) {
            $result = $model_stock_item->getData('qty');
        }

        return $result;
    }

    /**
     * Process deal_time follow pattern
     */
    public function getDealTime()
    {
        return $this->getData('deal_time');
    }

    public function insertProductExist($product_id, $start_date_time, $end_date_time)
    {
        $this->product_exist[$product_id][] = array(
            'start_date_time' => strtotime($start_date_time),
            'end_date_time' => strtotime($end_date_time),
        );
    }

    public function isValidProductExist($product_id, $start_date_time, $end_date_time)
    {
        $result = false;

        $products = $this->product_exist[$product_id];
        $start_date_time = strtotime($start_date_time);
        $end_date_time = strtotime($end_date_time);
        if (is_array($products)) {
            foreach ($products as $product) {
                if ($start_date_time >= $product['start_date_time'] && $start_date_time <= $product['end_date_time']) {
                    $result = true;
                    break;
                } elseif ($end_date_time >= $product['start_date_time'] && $end_date_time <= $product['end_date_time']) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

}