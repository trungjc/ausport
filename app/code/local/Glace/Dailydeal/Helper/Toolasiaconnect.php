<?php

/**
 * @author MR_TUAN
 */
class Glace_Dailydeal_Helper_Toolasiaconnect extends Mage_Core_Helper_Abstract
{

    const MODULE_NAME = 'dailydeal';
    const FILE_NAME = 'ToolAsiaConnect';

    /**
     * @return Glace_Dailydeal_Helper_Toolasiaconnect
     */
    public static function getInstance()
    {
        return Mage::helper(self::MODULE_NAME . '/' . self::FILE_NAME);
    }

    /**
     * Disable module
     * Override, add condition
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }
        if (!parent::isModuleOutputEnabled($moduleName)) {
            return false;
        }

        // Disable module
        if (!Mage::getStoreConfig(self::MODULE_NAME . '/general/enabled')) {
            return false;
        }

        return true;
    }

    /**
     * Convert array 'store_view' to string '1,2,3.,..' into insert database.
     * @return String
     */
    public function convertStoreViewToString($store_view = array())
    {
        $str_store_view = '';
        
        if(is_array($store_view)){
            if (in_array('0', $store_view)) {
                // if value equal 0 ( all ) only get 0
                $str_store_view = 0;
            } else {
                // convert value to 1,2,3,...
                $str_store_view = implode(',', $store_view);
            }
        }
        return $str_store_view;
    }

    /**
     * 
     * @param type $date_time '2013-04-13 04:51:15'
     * @param int $type
     * 1    : hours
     * 2    : minutes
     * 3    : seconds
     * 4    : month
     * 5    : days
     * 6    : year
     * 7    : week
     * @param string $format 'Y-m-d H:i:s'
     */
    public static function increaseTime($date_time, $type, $value, $format)
    {
        if ($value > 0) {
            $char = '+ ';
        }

        if ($type == 1) {
            $time = strtotime($char . $value . ' hours', strtotime($date_time));
        } elseif ($type == 3) {
            $time = strtotime($char . $value . ' seconds', strtotime($date_time));
        } elseif ($type == 5) {
            $time = strtotime($char . $value . ' days', strtotime($date_time));
        }

        $data = $time;
        if($format != null){
            $data = date($format, $time);
        }
            

        return $data;
    }

    public static function isValidPattern($pattern, $value)
    {
        $result = false;
        if (preg_match($pattern, $value)) {
            $result = true;
        }
        return $result;
    }
    
    /**
     * Array ( key => value )
     */
    public static function exploseEqualToArray( $str ){
        $result = array();
        
        $temp = explode("=", $str);
        $key  = $temp[0];
        $value = $temp[1];
        $result[$key] = $value;
        
        return $result;
    }
    
}