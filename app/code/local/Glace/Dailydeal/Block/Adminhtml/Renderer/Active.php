<?php

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
            '<span style="
    background-repeat: no-repeat;
    color: #FFFFFF;
    display: block;
    font: bold 10px/16px Arial,Helvetica,sans-serif;
    height: 16px;
    margin: 1px 0;
    padding: 0 0 0 7px;
    text-align: center;
    text-transform: uppercase;
    white-space: nowrap; 
    ">
                <span style="padding : 0 7px 0 0;color:#000000">' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_DISABLED] . '</span>
            </span>';
        }
        elseif ($active == Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED) {
            $return = 
            '<span class="grid-severity-critical">
                <span>' . $actives[Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED] . '</span>
            </span>';
        }

        return $return;
    }

}