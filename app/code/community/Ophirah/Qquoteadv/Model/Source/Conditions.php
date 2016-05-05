<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

class Ophirah_Qquoteadv_Model_Source_conditions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Returns an array of options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array(
            array(
                'value' => '0',
                'label' => 'Always show Add to Quote Button'
            ),
            array(
                'value' => '1',
                'label' => 'Show Add to Quote only when price is \'0.00\''
            )
        );
        return $options;
    }

    /**
     * Returns the label of a given option value
     *
     * @param $value
     * @return bool
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if (is_array($value)) {
                if (in_array($option['value'], $value)) {
                    return $option['label'];
                }
            } else {
                if ($option['value'] == $value) {
                    return $option['label'];
                }
            }
        }
        return false;
    }
}
