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
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Extended Sitemap extension
 *
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */
class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Grid_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $row->getSitemapFilename());
//        $code = Mage::app()->getStore($row->getStoreId())->getCode();
//        $url = $this->htmlEscape(str_replace('/index.php', '', Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK)) . $fileName);
        $url      = $this->htmlEscape(str_replace('/index.php', '',
                Mage::app()->getStore(0)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK)) . $fileName);
        if (file_exists(BP . DS . $fileName)) {
            return sprintf('<a href="%1$s" target = "_blank">%1$s</a>', $url);
        }
        return $url;
    }

}
