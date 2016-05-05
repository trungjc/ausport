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

class Mediarocks_RetinaImages_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get retina image JPG compression level
     *
     * @return int
     */
    public function getRetinaQuality()
    {
        $quality = Mage::getStoreConfig('retinaimages/module/quality');
        if (!$quality) {
            $quality = 60;
        }
        return ($quality > 100) ? 100 : (($quality < 1) ? 1 : $quality);
    }
    
    
    /**
     * Get default image JPG compression level
     *
     * @return int
     */
    public function getDefaultQuality()
    {
        $quality = Mage::getStoreConfig('retinaimages/module/default_quality');
        if (!$quality) {
            $quality = 90;
        }
        return ($quality > 100) ? 100 : (($quality < 1) ? 1 : $quality);
    }
}