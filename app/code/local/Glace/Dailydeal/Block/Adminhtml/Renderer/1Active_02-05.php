<?php

/**
 * Version 1 :
 *  - View 4 color follow magento
 */
class Glace_Dailydeal_Block_Adminhtml_Renderer_Active extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {

        $return = '';
        $active = $row->getData('active');
        $actives = Glace_Dailydeal_Model_Status::getStatusTimeOptionArray();
        
        if ($active == Glace_Dailydeal_Model_Status::STATUS_TIME_QUEUED) {
            $return = 
            '<span class="grid-severity-minor">
                <span>' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_QUEUED] . '</span>
            </span>';
        }
        elseif ($active == Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING) {
            $return = 
            '<span class="grid-severity-notice">
                <span>' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING] . '</span>
            </span>';
        }
        elseif ($active == Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED) {
            $return = 
            '<span class="grid-severity-critical">
                <span>' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED] . '</span>
            </span>';
        }
        elseif ($active == Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED) {
            $return = 
            '<span class="grid-severity-major">
                <span>' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED] . '</span>
            </span>';
        }

        return $return;
    }

}