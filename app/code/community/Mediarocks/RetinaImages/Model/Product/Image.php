<?php
/**
 * Media Rocks GbR
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled with 
 * this package in the file MEDIAROCKS-LICENSE-COMMUNITY.txt.
 * It is also available through the world-wide-web at this URL:
 * http://solutions.mediarocks.de/MEDIAROCKS-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package is designed for Magento COMMUNITY edition. 
 * Media Rocks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Media Rocks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please send an email to support@mediarocks.de
 *
 */

class Mediarocks_RetinaImages_Model_Product_Image extends Mage_Catalog_Model_Product_Image
{

    /**
     * resize function that checks for retina and makes the image twice as big
	 * 
     * @return Mage_Catalog_Model_Product_Image
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }
		
		$width = $this->_width;
		$height = $this->_height;
		
		if (Mage::registry('is_retina')) {
			$width = $width * 2;
			$height = $height * 2;
			if ($width == 0)
				$width = NULL;
			if ($height == 0)
				$height = NULL;
            
            $this->setQuality(Mage::helper('mediarocks_retinaimages')->getRetinaQuality());
		}
        else {
            $this->setQuality(Mage::helper('mediarocks_retinaimages')->getDefaultQuality());
        }
		
        $this->getImageProcessor()->resize($width, $height);
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function saveFile()
    {
        $filename = $this->getNewFile();
		if (Mage::registry('is_retina')) {
			$filename = $this->get2xFilename($filename);
            $this->setQuality(Mage::helper('mediarocks_retinaimages')->getRetinaQuality());
		}
        else {
            $this->setQuality(Mage::helper('mediarocks_retinaimages')->getDefaultQuality());
        }
        $this->getImageProcessor()->save($filename);
        Mage::helper('core/file_storage_database')->saveFile($filename);
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
		$filename = $this->_newFile;
		if (Mage::registry('is_retina')) {
			$filename = $this->get2xFilename($filename);
		}
        $path = str_replace($baseDir . DS, "", $filename);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }


    /**
     * @return bool
     */
    public function isCached()
    {
		$filename = $this->_newFile;
		if (Mage::registry('is_retina')) {
			$filename = $this->get2xFilename($filename);
		}
        return $this->_fileExists($filename);
    }
	
	/**
	 * get the retina-image filename
	 *
	 * @return string
	 */
	public function get2xFilename($filename)
	{
		return preg_replace('/(.*)(\.[\w\d]{3})/', '$1@2x$2', $filename);
	}
}
