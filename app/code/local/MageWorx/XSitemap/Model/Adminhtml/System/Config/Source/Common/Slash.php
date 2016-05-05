<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   design_default
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

class MageWorx_XSitemap_Model_Adminhtml_System_Config_Source_Common_Slash
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => Mage::helper('xsitemap')->__('Default')),
            array('value' => 'add', 'label' => Mage::helper('xsitemap')->__('Add')),
            array('value' => 'crop', 'label' => Mage::helper('xsitemap')->__('Crop')),
        );
    }

}
