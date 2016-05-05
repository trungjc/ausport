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

/**
 * It's mega class.
 * Class method getPermalinkPost() is modified getPermalink() from extended class Fishpig_Wordpress_Helper_Post.
 * Also it have method getPermalinkPage() - original method getPermalink() from Fishpig_Wordpress_Model_Resource_Page.
 * Both this method use modify getUrl() method, original from Fishpig_Wordpress_Helper_Abstract class.
 */
class MageWorx_XSitemap_Helper_Wordpress_Data extends Fishpig_Wordpress_Helper_Post
{
    const XML_CONFIG_WORDPRESS_INTEGRATION_FULL  = 'wordpress/integration/full';
    const XML_CONFIG_WORDPRESS_INTEGRATION_ROUTE = 'wordpress/integration/route';

    protected $_storeId;

    function init($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * Returns the URL used to access your Wordpress frontend
     * It's changed version of Fishpig_Wordpress_Helper_Abstract
     * @param string|null $extra = null
     * @param array $params = array
     * @return string
     */
    function getUrl($extra = null, array $params = array())
    {
        if (count($params) > 0) {
            $extra = trim($extra, '/') . '/';

            foreach ($params as $key => $value) {
                $extra .= $key . '/' . $value . '/';
            }
        }

        if ($this->isFullyIntegrated()) {
            $params = array(
                '_direct' => $this->getBlogRoute() . '/' . ltrim($extra, '/'),
                '_secure' => false,
                '_nosid'  => true,
                '_store'  => $this->_storeId,
            );

            $url = Mage::getUrl('', $params);
        }
        else {
            $url = $this->getWpOption('home') . '/' . ltrim($extra, '/');
        }

        return htmlspecialchars($url);
    }

    /**
     * It's changed version of Fishpig_Wordpress_Helper_Abstract
     */
    function getBlogRoute()
    {
        if ($this->isFullyIntegrated()) {
            return trim(strtolower(Mage::getStoreConfig(self::XML_CONFIG_WORDPRESS_INTEGRATION_ROUTE, $this->_storeId)),
                '/');
        }
        return null;
    }

    /**
     * It's changed version of Fishpig_Wordpress_Helper_Abstract
     */
    function isFullyIntegrated()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_WORDPRESS_INTEGRATION_FULL, $this->_storeId);
    }

    function getPermalink($item, $code = 'post')
    {
        if ($code == 'post') {
            return $this->getPermalinkPost($item);
        }
        if ($code == 'page') {
            return $this->getPermalinkPage($item);
        }
    }

    /**
     * return the  permalink based on permalink structure
     * which is defined from WP Admin
     *
     * It's changed version of parent method getPermalink()
     *
     * @param Fishpig_Wordpress_Model_Post
     * @return string
     */
    public function getPermalinkPost(Fishpig_Wordpress_Model_Post $post)
    {
        if ($this->useGuidLinks()) {
            return $this->getUrl('?p=' . $post->getId());
        }
        else {
            $structure = $this->_getExplodedPermalinkStructure();

            if (count($structure) > 0) {
                $url = array();

                foreach ($structure as $part) {
                    if (preg_match('/^\%[a-zA-Z0-9_]{1,}\%$/', $part)) {
                        $part = trim($part, '%');

                        if ($part === 'year') {
                            $url[] = $post->getPostDate('Y');
                        }
                        else if ($part === 'monthnum') {
                            $url[] = $post->getPostDate('m');
                        }
                        else if ($part === 'day') {
                            $url[] = $post->getPostDate('d');
                        }
                        else if ($part === 'hour') {
                            $url[] = $post->getPostDate('H');
                        }
                        else if ($part === 'minute') {
                            $url[] = $post->getPostDate('i');
                        }
                        else if ($part === 'second') {
                            $url[] = $post->getPostDate('s');
                        }
                        else if ($part === 'post_id') {
                            $url[] = $post->getId();
                        }
                        else if ($part === 'postname') {
                            $url[] = urldecode($post->getPostName());
                        }
                        else if ($part === 'category') {
                            $url[] = $this->_getPermalinkCategoryPortion($post);
                        }
                        else if ($part === 'author') {

                        }
                        else {
                            $response = new Varien_Object(array('value' => false));

                            Mage::dispatchEvent('wordpress_permalink_segment_unknown_getpermalink',
                                array('response' => $response, 'post'     => $post, 'segment'  => $part));

                            if ($response->getValue() !== false) {
                                $url[] = $response->getValue();
                            }
                        }
                    }
                    else {
                        if ($part === '/') {
                            $partCount = count($url);

                            if ($partCount > 0 && $url[$partCount - 1] === $part) {
                                continue;
                            }
                        }

                        $url[] = $part;
                    }
                }

                if ($this->permalinkHasTrainingSlash()) {
                    $url[count($url) - 1] .= '/';
                }
//                echo "<br>";
//                echo $url;
                return $this->getUrl(implode('', $url));
            }
        }
    }

    /**
     * Retrieve the permalink for a pge
     *
     * It's changed version of getPermalink() from Fishpig_Wordpress_Model_Resource_Page
     *
     * @param Fishpig_Wordpress_Model_Page $page
     * @return string
     */
    public function getPermalinkPage(Fishpig_Wordpress_Model_Page $page)
    {
        $uriParts = array();
        $buffer   = $page;

        do {
            $uriParts[] = $buffer->getPostName();
            $buffer     = $buffer->getParentPage();
        }
        while ($buffer && $buffer->getId());

        $parts = count($uriParts);

        if ($parts > 1) {
            $uriParts = array_reverse($uriParts);
        }

        if ($parts > 0) {
            return $this->getUrl(implode('/', $uriParts) . '/');
        }

        return '';
    }

}