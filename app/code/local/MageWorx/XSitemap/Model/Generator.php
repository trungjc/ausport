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
class MageWorx_XSitemap_Model_Generator
{
    /**
     * @var MageWorx_XSitemap_Model_Sitemap
     */
    protected $_model;

    /**
     * @var MageWorx_XSitemap_Helper_Data
     */
    protected $_helper;

    /**
     * @var MageWorx_XSitemap_Model_Writer
     */
    protected $_xmlWriter;
    protected $_entityName;
    protected $_storeBaseUrl;
    protected $_storeId;
    protected $_counter      = 0;
    protected $_totalProduct = 0;

    protected function _init(MageWorx_XSitemap_Model_Sitemap $model, $entityName)
    {
        $this->_entityName          = $entityName;
        $this->_model               = $model;
        $this->_storeId             = $model->getStoreId();
        $this->_helper              = Mage::helper('xsitemap');
        $this->_helper->init($this->_storeId);
        $this->_storeBaseUrl        = $this->_getStoreBaseUrl();
        $this->_storeBaseUrlTypeWeb = $this->_getStoreBaseUrlTypeWeb();
        $this->_initWriter($entityName);
    }

    protected function _initWriter($entityName)
    {
        $this->_xmlWriter = Mage::getModel('xsitemap/writer');
        $this->_xmlWriter->init($this->_model->getFullPath(), $this->_model->getSitemapFilename(),
            $this->_model->getFullTempPath(), $this->_isFirstStepGeneration($entityName),
            $this->_isEndStepGeneration($entityName), $this->_getStoreBaseUrlForSitemapIndex()
        );
    }

    protected function _isFirstStepGeneration($entityName)
    {
        // category - first entity name in entity name list when step by step generate xml (from GUI)
        return (!$entityName || $entityName == 'category') ? true : false;
    }

    protected function _isEndStepGeneration($entityName)
    {
        return (!$entityName || $entityName == 'sitemap_finish') ? true : false;
    }

    protected function _getTempPath()
    {

    }

    public function generateXml(MageWorx_XSitemap_Model_Sitemap $model, $entityName = false)
    {
        $this->_init($model, $entityName);

        if (!$this->_entityName || $this->_entityName == 'category') {
            $this->_generateXmlFromCategories();
        }

        if (!$this->_entityName || $this->_entityName == 'product') {
            $this->_generateXmlFromProducts();
        }

        if (!$this->_entityName || $this->_entityName == 'tag') {
            $this->_generateXmlFromProductTags();
        }

        if (!$this->_entityName || $this->_entityName == 'cms') {
            $this->_generateXmlFromCms();
        }

        if (!$this->_entityName || $this->_entityName == 'blog') {

            if ((string) Mage::getConfig()->getModuleConfig('AW_Blog')->active == 'true') {
                $this->_generateAwBlog();
            }

            if ((string) Mage::getConfig()->getModuleConfig('Fishpig_Wordpress')->active == 'true') {
                $this->_generateFishpigBlog();
            }
        }

        if (!$this->_entityName || $this->_entityName == 'additional_links') {
            $this->_generateFromAdditionalLinks();
        }

        if (!$this->_entityName || $this->_entityName == 'fishpig_attribute_splash_pages') {
            if ((string) Mage::getConfig()->getModuleConfig('Fishpig_AttributeSplash')->active == 'true') {
                $this->_generateFromFishpigAttributeSplashPages();
            }
        }

        if (!$this->_entityName || $this->_entityName == 'fishpig_attribute_splash_pro_pages') {
            if ((string) Mage::getConfig()->getModuleConfig('Fishpig_AttributeSplashPro')->active == 'true') {
                $this->_generateFromFishpigAttributeSplashProPages();
            }
        }

        if (!$this->_entityName || $this->_entityName == 'sitemap_finish') {
            //$this->_xmlWriter->closeXml();
            unset($this->_xmlWriter);
        }
    }

    protected function _generateFromFishpigAttributeSplashProPages()
    {
        if ($this->_helper->isFishpigAttributeSplashProGenerateEnabled()) {
            $changefreq     = $this->_helper->getFishpigAttributSplashProChangeFrequency();
            $priority       = $this->_helper->getFishpigAttributSplashProPriority();
            $splashProPages = $this->_helper->getFishpigAttributSplashProPages();
            if (count($splashProPages) > 0) {
                foreach ($splashProPages as $page) {
                    $url      = substr($page->getUrl(), strpos($page->getUrl(), $page->getUrlKey()));
                    $url      = $this->getStoreItemUrl($url);
                    $lastmode = $this->_helper->getFishpigAttributSplashProLastModifiedDate($page);
                    $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmode, $changefreq, $priority);
                }
            }
        }
    }

    protected function _generateFromFishpigAttributeSplashPages()
    {
        if ($this->_helper->isFishpigAttributeSplashGenerateEnabled()) {
            $changefreq_pages = $this->_helper->getFishpigAttributeSplashPageFrequency();
            $priority_pages   = $this->_helper->getFishpigAttributeSplashPagePriority();
            $changefreq_group = $this->_helper->getFishpigAttributeSplashGroupFrequency();
            $priority_group   = $this->_helper->getFishpigAttributeSplashGroupPriority();

            $splashPages = $this->_helper->getFishpigAttributSplashPages();

            if (count($splashPages) > 0) {
                foreach ($splashPages as $page) {
                    $page->setStoreId($this->_storeId);
                    $url      = $page->getUrl();
                    $lastmode = $page->getUpdatedAt(false);
                    $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmode, $changefreq_pages,
                        $priority_pages);
                }
            }


            if ($this->_helper->isFishpigAttributeSplashGroupPagesEnabled()) {
                $splashGroups = $this->_helper->getFishpigAttributSplashGroups();
                //echo count($splashGroups);

                if (count($splashGroups) > 0) {
                    foreach ($splashGroups as $group) {
                        if ($group->canDisplay()) {
                            if (Mage::app()->isSingleStoreMode() || $group->getStoreId() == $this->_storeId) {
                                $lastmode = $group->getUpdatedAt(false);
                                $url      = $group->getUrl();
                                $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmode,
                                    $changefreq_group, $priority_group);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function _generateFishpigBlog()
    {
        if ($this->_helper->isFishpigBlogEnabled() && $this->_helper->isBlogGenerateEnabled()) {
            $changefreq = $this->_helper->getBlogChangeFrequency();
            $priority   = $this->_helper->getBlogPriority();

            if ($this->_helper->isWordpressSingleStore()) {
                $url = htmlspecialchars(Mage::helper('wordpress')->getUrl());
            }
            else {
                $wordpressHelper = Mage::helper('xsitemap/wordpress_data');
                $wordpressHelper->init($this->_storeId);
                $url             = htmlspecialchars($wordpressHelper->getUrl());
            }

            $this->_xmlWriter->write($this->_helper->trailingSlash($url), $this->_getDate(), $changefreq, $priority);

            // Posts & Pages
            foreach (array('post', 'page') as $type) {
                $items = Mage::getResourceModel('wordpress/' . $type . '_collection')
                    ->addIsPublishedFilter()
                    ->setOrderByPostDate();

                if (count($items) > 0) {
                    foreach ($items as $item) {
                        if ($this->_helper->isWordpressSingleStore()) {
                            $url = htmlspecialchars($item->getPermalink());
                        }
                        else {
                            $url = htmlspecialchars($wordpressHelper->getPermalink($item, $type));
                        }
                        $lastmode = $item->getPostModifiedDate('Y-m-d') ? $item->getPostModifiedDate('Y-m-d') : $this->_getDate();
                        $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmode, $changefreq, $priority);
                    }
                    unset($items);
                }
            }
        }
    }

    protected function _generateAwBlog()
    {
        if ($this->_helper->isAwBlogEnabled() && $this->_helper->isBlogGenerateEnabled()) {
            $defaultRote = (string) Mage::getStoreConfig('blog/blog/route', $this->_storeId);
            if (!$defaultRote) {
                $defaultRote = 'blog';
            }
            $changefreq = $this->_helper->getBlogChangeFrequency();
            $priority   = $this->_helper->getBlogPriority();
            $collection = Mage::getResourceModel('xsitemap/blog_page')->getCollection($this->_storeId);
            foreach ($collection as $item) {
                list($dDate, $dTime) = explode(' ', $item->getDate());
                $url = htmlspecialchars($this->_storeBaseUrl . $defaultRote . "/" . $item->getUrl());
                $this->_xmlWriter->write($this->_helper->trailingSlash($url), $dDate, $changefreq, $priority);
            }
            unset($collection);
        }
    }

    protected function _generateFromAdditionalLinks()
    {
        $changefreq = $this->_helper->getLinkChangeFrequency();
        $priority   = $this->_helper->getLinkPriority();
        $addLinks   = array_filter(preg_split('/\r?\n/',
                $this->_helper->getAdditionalLinksForXmlSitemap($this->_storeId)));

        if (count($addLinks)) {
            foreach ($addLinks as $link) {
                if (strpos($link, ',') !== false) {
                    list($link) = explode(',', $link);
                }
                $link = trim($link);
                if (strpos($link, 'http') !== false) {
                    $links[] = new Varien_Object(array('url' => $link));
                }
                else {
                    list($url) = explode("/?",
                        Mage::getModel('core/store')->load($this->_storeId)->getUrl((string) $link));
                    $links[] = new Varien_Object(array('url' => $url));
                }
            }
        }

        if (!empty($links) && count($links)) {
            foreach ($links as $item) {
                $url     = $item->getUrl();
                $lastmod = $this->_getDate();
                $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority);
            }
            unset($links);
        }
    }

    protected function _generateXMLFromCategories()
    {
        $changefreq = $this->_helper->getCategoryChangeFrequency();
        $priority   = $this->_helper->getCategoryPriority();
        $collection = Mage::getResourceModel('xsitemap/catalog_category')->getCollection($this->_storeId);
        foreach ($collection as $item) {
            $model   = Mage::getModel('catalog/category')->load($item->getId());
            $url     = $this->_storeBaseUrl . $item->getUrl();
            $lastmod = $this->_getItemChangeDate($model);
            $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority);
        }

        unset($collection);
    }

    public function getProductImageUrl($imageFile)
    {
        return htmlspecialchars($this->_getStoreBaseUrlTypeWeb() . 'media/catalog/product' . $imageFile);
    }

    protected function _generateXmlFromProducts()
    {
        $this->_totalProduct = Mage::getResourceModel('xsitemap/catalog_product_xml')->getCollection($this->_storeId, true);
        $isProductImages     = $this->_helper->isProductImages();
        $changefreq          = $this->_helper->getProductChangeFrequency();
        $priority            = $this->_helper->getProductPriority();
        $limit               = $this->_helper->getXmlItemsLimit();

        if ($this->_entityName == "") {
            $limit = $this->_totalProduct;
        }

        if ($this->_counter < $this->_totalProduct) {

            if ($this->_counter + $limit > $this->_totalProduct) {
                $limit = $this->_totalProduct - $this->_counter;
            }
            $collection = Mage::getResourceModel('xsitemap/catalog_product_xml')
                ->getCollection($this->_storeId, false, $limit, $this->_counter);
            $this->_counter += $limit;

            foreach ($collection as $item) {
                //Custom canonical URL can content 'http[s]://'
                if(strpos(trim($item->getUrl()), 'http') === 0){
                    $url = $item->getUrl();
                }else{
                    $url = $this->_storeBaseUrl . $item->getUrl();
                }

                $lastmod = $this->_getItemChangeDate($item);
                if ($isProductImages) {
                    $imageUrl = array();
                    $gallery  = $item->getGallery();
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            $imageUrl[] = $this->getProductImageUrl($image['file']);
                        }
                    }
                    $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority,
                        $imageUrl);
                }
                else {
                    $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority);
                }
            }
            unset($collection);
        }
    }

    protected function _getItemChangeDate($model)
    {
        $upTime = $model->getUpdatedAt();
        if ($upTime == '0000-00-00 00:00:00') {
            $upTime = $model->getCreatedAt();
        }
        return substr($upTime, 0, 10);
    }

    protected function getStoreItemUrl($url)
    {
        //str_replace('//', '/', $this->_getStoreBaseUrl() . $url);
        return $this->_getStoreBaseUrl() . ltrim($url, '/');
    }

    protected function _getStoreBaseUrlForSitemapIndex()
    {
        return $this->_getStoreBaseUrlTypeWeb() . ltrim($this->_model->getSitemapPath(), '/');
    }

    protected function _getStoreBaseUrl()
    {
        $url = Mage::app()->getStore($this->_storeId)->getUrl();
        return (strpos($url, "?")) ? substr($url, 0, strpos($url, "?")) : $url;
    }

    protected function _getStoreBaseUrlTypeWeb()
    {
        $url = Mage::getModel('core/store')->load($this->_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        return (strpos($url, "?")) ? substr($url, 0, strpos($url, "?")) : $url;
    }

    protected function _getDate()
    {
        return Mage::getSingleton('core/date')->gmtDate('Y-m-d');
    }

    protected function _generateXmlFromProductTags()
    {
        if ($this->_helper->isProductTagsGenerateEnabled()) {
            $changefreq = $this->_helper->getProductTagsChangeFrequency();
            $priority   = $this->_helper->getProductTagsPriority();
            $collection = Mage::getModel('tag/tag')->getPopularCollection()
                ->joinFields($this->_storeId)
                ->load();

            foreach ($collection as $item) {
                $url     = str_replace($this->_storeBaseUrl . "index.php/", $this->_storeBaseUrl,
                    $item->getTaggedProductsUrl());
                $lastmod = $this->_getDate();
                $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority);
            }
            unset($collection);
        }
    }

        protected function _generateXmlFromCms()
    {
        $changefreq = $this->_helper->getPageChangeFrequency();
        $collection = Mage::getResourceModel('xsitemap/cms_page')->getCollection($this->_storeId);
        foreach ($collection as $item) {
            if ($item->getUrl() == "") {
                $priority = 1;
            }else{
            	$priority = $this->_helper->getPagePriority();
            }

            $url     = htmlspecialchars($this->_storeBaseUrl . $item->getUrl());
            $lastmod = $this->_getDate();
            $this->_xmlWriter->write($this->_helper->trailingSlash($url), $lastmod, $changefreq, $priority);
        }
        unset($collection);
    }

    public function setCounter($num)
    {
        $this->_counter = $num;
    }

    public function getCounter()
    {
        return $this->_counter;
    }

    public function getTotalProduct()
    {
        return $this->_totalProduct;
    }

}