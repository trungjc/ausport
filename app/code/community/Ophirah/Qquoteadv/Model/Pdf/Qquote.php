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

class Ophirah_Qquoteadv_Model_Pdf_Qquote extends Mage_Sales_Model_Order_Pdf_Abstract
{
    //highest point by vercical axis
    public $y = 750;

    //lowest position by vercical axis
    public $_minPosY = 15;

    public $_quoteadvId = null;
    public $_quoteadv = null;

    public $_leftRectPad = 45;
    public $_leftTextPad = 55;

    //save product name to avoid dublicate display produc't names 
    public $_prevItemName = '';
    public $_prevItemOptions = array();

    public $_itemNamePosY = 0;
    public $_itemId = null;
    public $columns = array();
    public $pdf;
    public $requestId;
    public $latestY = null;
    public $totalPrice = 0;
    public $isSetTierPrice = 0;

    // Show item price
    public $itemPriceReplace = ' ';
    public $rowTotalReplace = '--';

    // Image max width and max height
    public $defaultImg = 'thumbnail'; // options: 'image', 'small_image','thumbnail'
    public $imgWidth = 30;
    public $imgHeight = 30;

    protected $_currentPage = null;

    /**
     * Setter for _currentPage
     *
     * @param $page
     * @return $this
     */
    protected function setCurrentPage($page)
    {
        $this->_currentPage = $page;
        return $this;
    }

    /**
     * Getter for _currentPage
     *
     * @return null
     */
    protected function getCurrentPage()
    {
        return $this->_currentPage;
    }

//    protected function insertLogo(&$page, $store = null) {
//        $this->y-=40;
//        $imageWidth = 300; $imageHeight = 80;
//        $image = Mage::getStoreConfig('sales/identity/logo', $store);
//        if ($image) {
//            $imageFile = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
//            try {
//                $image = Zend_Pdf_Image::imageWithPath($imageFile,$imageWidth,$imageHeight);
//                $x = $this->_leftRectPad; $y = $this->y;
//                $page->drawImage($image, $x, $y, $x+$imageWidth, $y+$imageHeight);
//
//            } catch(Exception $e) {
//                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
//            }
//        }
//    }

    /**
     * Adds the store logo to the pdf
     *
     * @param $page
     * @param null $store
     */
    protected function insertLogo(&$page, $store = null)
    {

        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $this->y = 780;
            $x = $this->_leftRectPad;
            $y = $this->y;
            $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, $x, $y, $x + 200, $y + 50);
            }
        }
        //return $page;
    }

    /**
     * Function that inserts the address on the PDF page
     *
     * @param $page
     * @param null $store
     */
    protected function insertAddress(&$page, $store = null)
    {
        $y = 780 + 20;
        $x = $this->_leftRectPad + 345;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 7);

        $itemRequest = wordwrap(Mage::getStoreConfig('sales/identity/address', $store), 50, "\n");
        foreach (explode("\n", $itemRequest) as $value) {
            if ($value !== '') {
                $value = str_replace("\r", "", $value);
                $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
                $y -= 7;
            }
        }
        #case when address text height much more logo height
//        if($this->y > $y){ 
//            $this->y = $y;  
//        }
    }

    /**
     * Adds a new page to the PDF
     *
     * @return mixed
     */
    public function addNewPage()
    {
        /* Add new table head */
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;
        $this->setCurrentPage($page);
        $this->y = 800;

        $this->_setFontRegular($page);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        foreach ($this->columns as $item) {
            $textLabel = $item[0];
            $xx = $item[1];
            $yy = $this->y;
            $textEncod = $item[3];

            $page->drawText($textLabel, $xx, $yy, $textEncod);
        }

        $this->y -= 20;
        return $page;
    }

    /**
     * Function that generates the PDF for a give quote
     *
     * @param array $quotes
     * @return Zend_Pdf
     */
    public function getPdf($quotes = array())
    {
        $this->_beforeGetPdf();

        $this->pdf = new Zend_Pdf();
        $style = new Zend_Pdf_Style();

        $this->_setFontBold($style, 10);

        if ($quotes instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            $this->_quoteadvId = $quotes->getId();
            $this->_quoteadv = $quotes;
        } else {
            foreach ($quotes as $item) {
                $this->_quoteadvId = $item['quote_id'];
            }

            $quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($this->_quoteadvId);
            $quoteadv->collectTotals();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $quoteadv));
            $quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $quoteadv));
            $this->_quoteadv = $quoteadv;

        }

        if ($this->_quoteadv->getStoreId()) {
            Mage::app()->getLocale()->emulate($this->_quoteadv->getStoreId());
        }

        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->setCurrentPage($page);
        $page = $this->getCurrentPage();
        $this->pdf->pages[] = $page;


        /* Add image */
        $this->insertLogo($page, $this->_quoteadv->getStoreId());

        /* Add address */
        $this->insertAddress($page, $this->_quoteadv->getStoreId());

        /* Add head */
        $this->insertTitles($page, $this->_quoteadvId, $this->_quoteadv->getStoreId());

        // BETA FEATURE
//        if (Mage::helper('qquoteadv')->betaIsEnabled($this->_quoteadv->getData('store_id'))) {
            /* Add Available Shipping Methods */
            $this->insertShippingMethods();
//        } else {
//            // Create margin
//            $this->y -= 10;
//        }
        // END BETA FEATURE

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);

        $rectHeight = 15;
        /* Add table */
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y - $rectHeight);

        $this->y -= 10;
        /* Add table head */
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));

        $this->columns = array(
            array(Mage::helper('catalog')->__('Product image'), $this->_leftTextPad, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('Product name'), 100, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('SKU'), 290, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('QTY'), 410, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('Price'), 460, $this->y, 'UTF-8'),
            array(Mage::helper('adminhtml')->__('Subtotal'), 520, $this->y, 'UTF-8')
        );

        //#draw TABLE TITLES
        foreach ($this->columns as $item) {
            $textLabel = $item[0];
            $textPosX = $item[1];
            $textPosY = $item[2];
            $textEncod = $item[3];

            $page->drawText($textLabel, $textPosX, $textPosY, $textEncod);
        }

        $this->y -= 15;
        if ($this->_minPosY + 60 > $this->y) {
            $page = $this->addNewPage();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        $requestItems = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $this->_quoteadvId);

        /* Add body */
        foreach ($requestItems as $product) {
            if ($this->_minPosY + 90 > $this->y) {
                $page = $this->addNewPage();
            }
            /* Draw item */
            $page = $this->draw($product, $page);
        }

        if ($this->_minPosY + 60 > $this->y) {
            $page = $this->addNewPage();
        }

        /* Add total */
        $page = $this->getCurrentPage();
        $this->insertTotal($page);

        if ($this->_minPosY + 30 > $this->y) {
            $page = $this->addNewPage();
        }

        /* Add quote2cart general remark */
        $this->insertGeneralRemark($page);

        if ($this->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Function that renders a configurable product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderConfigurable($product, $item)
    {
        $html = array();

        $x = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());

        foreach ($x->getAllItems() as $_zz) {
            if ($_zz->getProductId() == $product->getId()) {
                $obj = new Ophirah_Qquoteadv_Block_Item_Renderer_Configurable;
                $obj->setTemplate('qquoteadv/item/configurable.phtml');
                $obj->setItem($_zz);

                if ($_options = $obj->getOptionList()) {
                    foreach ($_options as $_option) {
                        $_formatedOptionValue = $obj->getFormatedOptionValue($_option);
                        $html[] = $_option['label'];
                        $html[] = '  ' . $_formatedOptionValue['value'];
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Function that renders a downloadable product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderDownloadable($product, $item)
    {
        $html = array();
        $qqadvproductdownloadable =  Mage::getModel('qquoteadv/qqadvproductdownloadable');
        $html[] = 'Links:';

        $qqadvproductdownloadable->loadProduct($item);
        if ($links = $qqadvproductdownloadable->getLinks()) {
            foreach ($links as $link) {
//                    $_formatedOptionValue = $obj->getFormatedOptionValue($_option);
                $html[] = $link->getTitle();
//                    $html[] = '  ' . $_formatedOptionValue['value'];
            }
        }

        return $html;
    }

    /**
     * Function that renders a bundle product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderBundle($product, $item)
    {
        $html = array();
        $product->setStoreId($item->getStoreId() ? $item->getStoreId() : 1);

        $virtualQuote = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());
        $_helper = Mage::helper('bundle/catalog_product_configuration');

        foreach ($virtualQuote->getAllItems() as $_unit) {
            if ($_unit->getProductId() == $product->getId()) {

                $_options = $_helper->getOptions($_unit);
                if (is_array($_options)) {

                    foreach ($_options as $_option):

                        //$_formatedOptionValue = $this->getFormatedOptionValue($_option);
                        $helperx = Mage::helper('catalog/product_configuration');
                        $params = array(
                            'max_length' => 55,
                            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
                        );
                        $x = $helperx->getFormattedOptionValue($_option, $params);

                        $html[] = $_option['label'];

                        $simple = explode("\n", $x['value']);
                        foreach ($simple as $opt) {
                            $opt = str_replace("\r", "", $opt);
                            $html[] = '  ' . $opt;
                        }

                    endforeach;

                }
            }
        }

        return $html;
    }

    /**
     * For some reason some sites use the wrong extension type in the url like .jpg for a .png image...
     * support for gif, jpg/jpeg and png
     */
    public function checkImageExtAndType($imagePath){
        if (function_exists('exif_imagetype')) {
            //get extention from pathinfo
            $ext = pathinfo($imagePath, PATHINFO_EXTENSION);

            //gif
            if(strcasecmp('gif', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_GIF){
                    return true;
                } else {
                    return false;
                }
            }

            //jpg
            if(strcasecmp('jpg', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_JPEG){
                    return true;
                } else {
                    return false;
                }
            }

            //jpeg
            if(strcasecmp('jpeg', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_JPEG){
                    return true;
                } else {
                    return false;
                }
            }

            //png
            if(strcasecmp('png', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_PNG){
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            //if no exif support, always return true
            return true;
        }

        return null;
    }

    /**
     * Draws a product on the give page
     *
     * @param $unit
     * @param $page
     * @return Zend_Pdf_Page
     */
    public function draw($unit, $page)
    {
        $showPrice = Mage::helper('qquoteadv')->isPriceByDefaultAllowed();

        $line = array();
        $drawItems = array();
        $lineHeight = 10; // characters Line height
        $maxChar = 30; // Max characters to split string

        $this->_setFontRegular($page);

        $productId = $unit->getProductId();
        $itemRequest = $unit->getClientRequest();

        if (!$productId){
            return null;
        }

        /** @var Mage_Catalog_Model_Product $item */
        $item = Mage::getModel('catalog/product')->load($productId);
        $imageItem = $item;

        if ($item->getTypeId() == 'bundle') {
            $attr = $this->_renderBundle($item, $unit);
        } elseif ($item->getTypeId() == 'configurable') {
            $attr = $this->_renderConfigurable($item, $unit);
            // Load configured simple for correct product image
            $imageItem = $unit->getConfChildProduct($unit->getId());
        } elseif($item->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE){
            $attr = $this->_renderDownloadable($item, $unit);
        }else{
            $superAttribute = $this->getOption($item, $unit->getAttribute());

            //render custom options 
            $attr = $this->retrieveOptions($item, $superAttribute);
        }

        // Draw product image
        $prodImage = (string)Mage::getBaseDir('media') . '/catalog/product' . $imageItem->getData($this->defaultImg);
        if (is_file($prodImage)) {
            // get picture dimensions
            $image = Mage::helper('catalog/image')->init($imageItem, $this->defaultImg);
            if(!is_object($image) && !get_class($image) == 'Mage_Catalog_Helper_Image') {
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($image, $this->imgWidth, $this->imgHeight);
            } else {
                //file fallback
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($prodImage, $this->imgWidth, $this->imgHeight);
            }

            $x = $this->_leftRectPad;
            $y = $this->y - ($newDim['height'] - ($lineHeight - 3)); // aligning top of the image with text

            if (function_exists('exif_imagetype')) {
                if (exif_imagetype($prodImage) == IMAGETYPE_GIF) {
                    //image is GIF
                    $imageNameWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($prodImage));
                    $imagePath = sys_get_temp_dir() . '/' . $imageNameWithoutExt . '.png';
                    imagepng(imagecreatefromgif($prodImage), $imagePath);
                    $pdfimage = Zend_Pdf_Image::imageWithPath($imagePath);
                    unlink($imagePath);
                } else {
                    //image is other standard supported file
                    $pdfimage = Zend_Pdf_Image::imageWithPath($prodImage);
                }
            } else {
                //No exif support
                $ext = pathinfo($prodImage, PATHINFO_EXTENSION);
                if(strcasecmp('gif', $ext) == 0) {
                    //image is GIF
                    $imageNameWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($prodImage));
                    $imagePath = sys_get_temp_dir() . '/' . $imageNameWithoutExt . '.png';
                    imagepng(imagecreatefromgif($prodImage), $imagePath);
                    $pdfimage = Zend_Pdf_Image::imageWithPath($imagePath);
                    unlink($imagePath);
                } else {
                    //image is other standard supported file
                    $pdfimage = Zend_Pdf_Image::imageWithPath($prodImage);
                }
            }

            $page->drawImage($pdfimage, $x, $y, $x + $newDim['width'], $y + $newDim['height']);
        } else {
            //posible image url?
            $imageUrl = $imageItem->getData($this->defaultImg);
            if ($imageUrl !== 'no_selection' && !empty($imageUrl)) {
                if(is_file($imageUrl)){
                    if(file_get_contents($imageUrl, 0, null, 0, 1)){
                        // url has image
                        $imagePath = sys_get_temp_dir() . '/' . basename($imageUrl);
                        file_put_contents($imagePath, file_get_contents($imageUrl));

                        if (function_exists('exif_imagetype')) {
                            if(exif_imagetype($imagePath) == IMAGETYPE_GIF){
                                //image is GIF
                                $imagePathWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imagePath);
                                $imageFromGif = imagecreatefromgif($imagePath);
                                unlink($imagePath);

                                $imagePath = $imagePathWithoutExt.'.png';
                                imagepng($imageFromGif, $imagePath);
                            }
                        } else {
                            //No exif support
                            $ext = pathinfo($prodImage, PATHINFO_EXTENSION);
                            if(strcasecmp('gif', $ext) == 0) {
                                //image is GIF
                                $imagePathWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imagePath);
                                $imageFromGif = imagecreatefromgif($imagePath);
                                unlink($imagePath);

                                $imagePath = $imagePathWithoutExt.'.png';
                                imagepng($imageFromGif, $imagePath);
                            }
                        }

                        if($this->checkImageExtAndType($imagePath)){
                            // get picture dimensions
                            $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($imagePath, $this->imgWidth, $this->imgHeight);

                            $x = $this->_leftRectPad;
                            $y = $this->y - ($newDim['height'] - ($lineHeight - 3)); // aligning top of the image with text

                            $pdfimage = Zend_Pdf_Image::imageWithPath($imagePath);

                            unlink($imagePath);

                            $page->drawImage($pdfimage, $x, $y, $x + $newDim['width'], $y + $newDim['height']);
                        }
                    }
                }
            }
        }

        /* in case Product name is longer than 55 chars - it is written in a few lines */
        $name = $item->getName();
        $line[] = array(
            'text' => Mage::helper('core/string')->str_split(strip_tags($name), $maxChar, true, true),
            'feed' => ($this->_leftTextPad + $this->imgWidth + 10),
            'font' => 'bold',
            'font_size' => 9,
            'height' => $lineHeight
        );

        // draw SKUs
        $sku = $this->getSku($item);
        $text = array();
        foreach (Mage::helper('core/string')->str_split($sku, 30) as $part) {
            $text[] = $part;
        }
        $line[] = array(
            'text' => $text,
            'feed' => 290
        );

        $requestedProductData = Mage::getModel('qquoteadv/requestitem')
            ->getCollection()->setQuote($this->_quoteadv)
            ->addFieldToFilter('quote_id', $unit->getQuoteId())
            ->addFieldToFilter('quoteadv_product_id', $unit->getId())
            ->addFieldToFilter('product_id', $productId);
        $requestedProductData->getSelect()->order(array('product_id ASC', 'request_qty ASC'));
        // create Tier array with prices
        foreach ($requestedProductData as $reqProduct) {
            $tierPrices[$reqProduct['request_qty']] = $reqProduct['owner_cur_price'];
        }

        $_quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($unit->getQuoteId());
        $currency = $_quote->getData('currency');
        $currentCurrencyCode = Mage::app()->getStore($_quote->getStoreId())->getCurrentCurrencyCode();
        Mage::app()->getStore($_quote->getStoreId())->setCurrentCurrencyCode($currency);

        //#tier price section
        $k = 0;
        $showCurrentTier = false; // set this to true to show current tier in sub tier list
        $txt1 = $txt2 = $txt3 = array();
        // Setting first price
        if ($showPrice) {
            //Mage::app()->getStore($_quote->getStoreId())->formatPrice(
            $price = Mage::app()->getStore($_quote->getStoreId())->formatPrice($tierPrices[$unit->getQty()], false);
            $row = $unit->getQty() * $tierPrices[$unit->getQty()];
            $rowTotal = Mage::app()->getStore($_quote->getStoreId())->formatPrice($row, false);
        } else {
            $price = $this->itemPriceReplace;
            $rowTotal = $this->rowTotalReplace;
        }

        $size = 6;
        $txt1[] = array('text' => $unit->getQty(), 'font' => 'regular', 'font_size' => $size);
        $txt2[] = $price; //480
        $txt3[] = $rowTotal; //542

        if (count($requestedProductData) > 1):
            foreach ($requestedProductData as $product) {
                if ($k > 0) {
                    $this->isSetTierPrice = 1;
                }
                //set first line
                $productQty = $product->getRequestQty() * 1;
                $priceProposal = $product->getOwnerCurPrice();

                $showTier = true;
                $price = Mage::app()->getStore($_quote->getStoreId())->formatPrice($priceProposal, false);
                $row = $productQty * $priceProposal;
                $rowTotal = Mage::app()->getStore($_quote->getStoreId())->formatPrice($row, false);
                if ($productQty == $unit->getQty()) {
                    $rowTotal .= "*";
                    if ($showCurrentTier === false) {
                        $showTier = false;
                    }
                }

                // add row total
                $this->totalPrice += $row;

                if ($showTier === true) {
                    if (!$showPrice) {
                        $price = $this->itemPriceReplace;
                        $rowTotal = $this->rowTotalReplace;
                    }
                    $size = 5;
                    $font = 'italic';
                    $txt1[] = array('text' => $productQty, 'font' => $font, 'font_size' => $size); //405
                    $txt2[] = array('text' => $price, 'font' => $font, 'font_size' => $size); //480
                    $txt3[] = array('text' => $rowTotal, 'font' => $font, 'font_size' => $size); //542
                }
                $k++;
            }
        endif;

        Mage::app()->getStore($_quote->getStoreId())->setCurrentCurrencyCode($currentCurrencyCode);

        $line[] = array(
            'text' => $txt1,
            'feed' => 410
        );

        $line[] = array(
            'text' => $txt2,
            'feed' => 460
        );

        $line[] = array(
            'text' => $txt3,
            'feed' => 520
        );

        $drawItems[0]['lines'][] = $line;
        unset($line);
        //#section 2
        $desc = Mage::getStoreConfig('qquoteadv_quote_emails/attachments/short_desc', $this->_quoteadv->getStoreId());
        if ($desc) {
            $shortDesc = strip_tags($item->getShortDescription());
            $shortDesc = str_replace("&nbsp;", ' ', $shortDesc);
            $shortDesc = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $shortDesc);
            $line[] = array(
                'text' => Mage::helper('core/string')->str_split($shortDesc, 80, true, true),
                'feed' => $this->_leftTextPad,
                'font' => 'italic',
                'font_size' => 6,
                'height' => 7
            );
            $drawItems[1]['lines'][] = $line;
            unset($line);
        }

        //#section 3
        $text = array();
        if (count($attr) > 0) {

            foreach ($attr as $value) {
                if ($value !== '') {
                    $value = str_replace('&quot;', '"', $value);
                    $value = strip_tags($value);
                    $str = Mage::helper('core/string')->str_split($value, 55, true, true);
                    foreach ($str as $valueA) {
                        if (!empty($valueA)) {
                            $text[] = $valueA;
                        }
                    }
                }
            }
        }

        $itemRequest = strip_tags($itemRequest);
        $itemRequest = wordwrap($itemRequest, 80, "\n");
        foreach (explode("\n", $itemRequest) as $value) {
            if (!empty($value)) {
                $value = str_replace("\r", "", $value);
                $text[] = $value;
            }
        }
        $text[] = '';
        $line[] = array(
            'text' => $text,
            'feed' => ($this->_leftTextPad + $this->imgWidth + 10),
            'font' => 'italic',

        );
        $drawItems[2]['lines'][] = $line;
        $page = $this->drawLineBlocks($page, $drawItems, array('table_header' => true));

        return $page;
    }

    /**
     * Function that inserts the title/header on a page
     *
     * @param $page
     * @param $source
     * @param $storeId
     */
    protected function insertTitles(&$page, $source, $storeId)
    {
        $quoteadvId = $source;
        if (is_null($this->_quoteadv)) {
            $this->_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteadvId);
        }

        $this->y -= 30;
        $y = $this->y;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->setLineWidth(0.5);

        //height of address box is determined by the last value
        $page->drawRectangle($this->_leftRectPad, $y, 570, $this->y -= 65);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $topPosY = $y - 10;
        $page->drawText(Mage::helper('sales')->__('To:'), $this->_leftTextPad, $topPosY, 'UTF-8');

//        $shippingCompany = $this->_quoteadv->getShippingCompany();
//        $value = trim($shippingCompany);
//        if(!empty($value)) {
//            $y-=10;
//            $this->_setFontBold($page);
//            $page->drawText($shippingCompany, $this->_leftTextPad + 20, $y, 'UTF-8'); 
//        }
        $y -= 10;

        /*$shipTo = $this->reformatAddress($this->_quoteadv->getAddressFormatted(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING));

        $this->_setFontRegular($page);
        foreach ($shipTo as $value) {
            if ($value !== '') {
                $page->drawText($value, $this->_leftTextPad + 20, $y, 'UTF-8');
                $y -= 10;
            }
        }*/
        $shippingAddress = $this->_formatAddress($this->_quoteadv->getShippingAddressFormatted('pdf'));
        foreach ($shippingAddress as $value){
            if ($value!=='') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)),  $this->_leftTextPad + 20, $y, 'UTF-8');
                    $y -= 10;
                }
            }
        }

        $x = 400;
        $xPad = $x + 80;
        $y = $topPosY;

        $page->drawText(Mage::helper('qquoteadv')->__('Quote Proposal'), $x, $topPosY, 'UTF-8');

        $realQuoteadvId = $this->_quoteadv->getIncrementId() ? $this->_quoteadv->getIncrementId() : $this->_quoteadv->getId();
        $page->drawText($realQuoteadvId, $xPad, $y, 'UTF-8');

        $y -= 10;
//        $proposalDate = $this->_quoteadv->getProposalSent();  //Mage::helper('core')->formatDate( date( 'D M j Y')

        $proposalDate = $this->_quoteadv->getProposalDate();
        $page->drawText(Mage::helper('qquoteadv')->__('Date of Proposal'), $x, $y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate($proposalDate, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, false), $xPad, $y, 'UTF-8');

        if ($expiryDate = $this->_quoteadv->getExpiry()) {
            // PHP > 5.2 && PHP < 5.3
            $expDays = (int)round((date_create($expiryDate)->format("U") - date_create($proposalDate)->format("U")) / (60 * 60 * 24));
            // PHP >= 5.3
//            $expDays    = (int) date_diff(date_create($proposalDate), date_create($expiryDate))->format('%a');
            $validDate = date('D M j Y', Mage::getModel('core/date')->timestamp(strtotime($expiryDate)));
        } else {
            $expDays = (int)Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal', $this->_quoteadv->getStoreId());
            $validDate = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
        }
        if ($expDays && $validDate) {
            $y -= 10;
            $page->drawText(Mage::helper('qquoteadv')->__('Proposal valid until'), $x, $y, 'UTF-8');
            $validDate = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
            $note = "( " . Mage::helper('qquoteadv')->__("%s days", $expDays) . " )";
            $value = Mage::helper('core')->formatDate($validDate, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, false) . '    ' . $note;
            $page->drawText($value, $xPad, $y, 'UTF-8');
        }

        $this->_setFontRegular($page);
    }

    // Available shipping Methods
    public function insertShippingMethods()
    {

        // Get Shipping Methods
        $shippingRates = Mage::getModel('qquoteadv/quoteshippingrate')->getShippingRatesList($this->_quoteadv);
        $shippingRateList = $shippingRates['shippingList'];

        //remove quote rate
        foreach ($shippingRateList as $key => $rates){
            foreach ($rates as $rate){
                if($rate['code'] == "qquoteshiprate_qquoteshiprate"){
                    unset($shippingRateList[$key]);
                }
            }
        }

        $itemCount = $shippingRates['itemCount'];

        // Declare position variables
        $lineHeight = 10;
        $maxWidth = 570;
        $boxHeader = 15;
        $boxMargin = array('top' => 10, 'right' => 0, 'bottom' => 10, 'left' => 0);
        $boxPadding = array('top' => 10, 'right' => 5, 'bottom' => 5, 'left' => 0);
        $boxBody['height'] = (($itemCount + 1) * $lineHeight) + $boxPadding['top'] + $boxPadding['bottom'];
        $boxBody['width'] = $maxWidth - $boxMargin['left'] - $boxMargin['right'];

        $fontHead = 7;
        $fontIndent = 20;

        // Setting Margin
        $this->y -= $boxMargin['top'];
        $y = $this->y;

        // TODO: Add message if no shipping methods are available
        // for now the whole box is hidden
        if ($shippingRates && count($shippingRateList) > 0) {
            // Get current page
            $page = $this->getCurrentPage();

            // Setting new y position
            $this->y -= ($boxHeader + $boxBody['height']);

            // Box Border 
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            // Draw Box Header
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y, $boxBody['width'], $y - $boxHeader);

            // Draw Box Body
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y - $boxHeader, $boxBody['width'], $y - $boxBody['height']);

            // Insert Header Text
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page);
            $topPosY = $y - 10;
            $page->drawText(Mage::helper('qquoteadv')->__('Available Shipping Methods'), $this->_leftTextPad, $topPosY, 'UTF-8');

            // Draw Shipping Rates
            $topPosY = $y - ($boxHeader + $boxPadding['top']);
            foreach ($shippingRateList as $k => $v):
                $posLeft = $this->_leftTextPad + $boxMargin['left'] + $boxPadding['left'];
                // Draw Carrier Title
                $this->_setFontBold($page, $fontHead);
                $page->drawText($k, $posLeft, $topPosY, 'UTF-8');
                $topPosY = $topPosY - $lineHeight;
                foreach ($v as $rate) {
                    $this->_setFontRegular($page);
                    if($rate['method_list'] == ''){
                        $ratePieces = explode("_", $rate['code']);

                        if(isset($ratePieces[1])){
                            $title = $ratePieces[1];
                        } else {
                            if (!$title = Mage::getStoreConfig("carriers/" . $ratePieces[0] . "/title")) {
                                $title = $rate['code'];
                            }
                        }
                        $rate['method_list'] = $title;
                    }
                    $line = uc_words($rate['method_list']) . ' - ' . strip_tags($this->_quoteadv->formatPrice($rate['price']));
                    $page->drawText($line, $posLeft + $fontIndent, $topPosY, 'UTF-8');
                    $topPosY = $topPosY - $lineHeight;
                }
            endforeach;
        }

    }

    /**
     * Function that draws a label on a given position
     *
     * @param $label
     * @param $position
     * @return $this
     */
    protected function _drawLabel($label, $position)
    {
        $page = $this->getCurrentPage();
        $this->_setFontBold($page, 7);
        $page->drawText($label, $position, $this->y, 'UTF-8');
        $this->_setFontRegular($page);
        return $this;
    }

    /**
     * Function that draws text on a given position
     *
     * @param $text
     * @param $position
     * @return $this
     */
    protected function _drawText($text, $position)
    {
        $page = $this->getCurrentPage();
        $page->drawText($text, $position, $this->y, 'UTF-8');
        return $this;
    }

    /**
     * Function that draws the total price
     *
     * @param $label
     * @param $price
     * @param int $storeId
     */
    protected function drawTotal($label, $price, $storeId = 0)
    {
        $text = Mage::app()->getStore($storeId)->formatPrice($price, false);
        $this->_drawLabel($label, 390, $this->y, 'UTF-8');
        $this->_drawText(strip_tags($text), 520, $this->y, 'UTF-8');
        $this->y -= 12;
    }

    /**
     * Total information by quoteadv
     *
     * @param  $page
     */
    protected function insertTotal(&$page)
    {
        $this->y -= 20;
        $currentY = $this->y;
        $totalsArray = $this->_quoteadv->getTotalsArray();

        /* $excl = Mage::helper('qquoteadv')->__('(excl. TAX)');

         //if(!$this->isSetTierPrice){
             $this->_setFontBold($page,9);
             $sLabel     = Mage::helper('qquoteadv')->__('Total price')." ".$excl;
             $totalPrice = Mage::app()->getStore($_quote->getStoreId())->formatPrice($this->totalPrice, false);

             $page->drawText($sLabel, 430, $this->y, 'UTF-8');
             $page->drawText(strip_tags($totalPrice), 530, $this->y, 'UTF-8');

             $this->y -= 10;        */
        //}

        $store = $this->_quoteadv->getStore();

        $taxConfig = Mage::getSingleton('tax/config');


        // Adding Quote Adjustment Total
        // To default totals
        if (Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/adjustment', $store) == 1) {

            if (Mage::getStoreConfig('tax/calculation/price_includes_tax', $store) == 1) {
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote (Incl. default Tax)');
            } else {
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote');
            }

            $reduction = $this->_quoteadv->getQuoteReduction();
            if ($reduction != false) {
                $price = -1 * $reduction;
                $this->drawTotal($label, $price, $store->getStoreId());
            }
        }

        if ($taxConfig->displaySalesSubtotalExclTax($store)) {

            $label = Mage::helper('tax')->__('Subtotal');
            $price = $this->_quoteadv->getSubtotal();
            $this->drawTotal($label, $price, $store->getStoreId());

        }

        if ($taxConfig->displaySalesSubtotalInclTax($store)) {

            $label = Mage::helper('tax')->__('Subtotal');
            $price = $this->_quoteadv->getSubtotalInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());

        }


        if ($taxConfig->displaySalesSubtotalBoth($store)) {

            $label = Mage::helper('tax')->__('Subtotal (Excl. Tax)');
            $price = $this->_quoteadv->getSubtotal();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Subtotal (Incl. Tax)');
            $price = $this->_quoteadv->getSubtotalInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        /*        if($this->_quoteadv->getShippingType() ==""):
                    $label= Mage::helper('tax')->__('Shipping & Handling');
                    $text = Mage::helper('qquoteadv')->__('Select in Checkout');
                    $this->_drawLabel($label, 390, $this->y, 'UTF-8');
                    $this->_drawText(strip_tags($text), 520, $this->y, 'UTF-8');
                    $this->y -= 12;

                else:
        */
        if ($taxConfig->displaySalesShippingInclTax($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Incl. Tax)');
            $price = $this->_quoteadv->getShippingInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesShippingExclTax($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Excl. Tax)');
            $price = $this->_quoteadv->getShippingAmount();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesShippingBoth($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Excl. Tax)');
            $price = $this->_quoteadv->getShippingAmount();
            $this->drawTotal($label, $price, $store->getStoreId());
            $label = Mage::helper('tax')->__('Shipping & Handling (Incl. Tax)');
            $price = $this->_quoteadv->getShippingAmountInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

//        endif;

        // Fooman Surcharge
        if(isset($totalsArray['surcharge'])){
            $surcharge = $totalsArray['surcharge'];
            $label = $surcharge['title'];
            $price = $surcharge['value'];
            $this->drawTotal($label, $price);
        }

        if (isset($totalsArray['discount'])) {
            $discount = $totalsArray['discount'];
            $label = $discount['title'];
            $price = $discount['value'];
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesTaxWithGrandTotal($store)) {
            $label = Mage::helper('tax')->__('Grand Total (Excl. Tax)');
            $price = $this->_quoteadv->getGrandTotalExclTax();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Tax');
            $price = $this->_quoteadv->getTaxAmount();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Grand Total (Incl. Tax)');
            $price = $this->_quoteadv->getGrandTotal();
            $this->drawTotal($label, $price, $store->getStoreId());

        } else {
            $label = Mage::helper('tax')->__('Tax');
            $price = $this->_quoteadv->getTaxAmount();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Grand Total');
            $price = $this->_quoteadv->getGrandTotal();
            $this->drawTotal($label, $price, $store->getStoreId());
        }


        $this->y = $currentY;

        $remark = $this->_quoteadv->getClientRequest();
        if ($remark) {
            $remark = strip_tags($remark);
            $remark = wordwrap($remark, 55, "\n");
            $data = explode("\n", $remark);
        }

        $min_height = 55;
        if (isset($data)) {
            $boxheight = count($data) * 7;
        } else {
            $boxheight = 0;
        }

        if ($boxheight > $min_height) {
            $lowPoint = $this->y - ($boxheight + 10);
        } else {
            $lowPoint = $this->y - ($min_height + 10);
        }

//        $lowPoint = $this->y-55;

        if (isset($data)) {

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
            $page->setLineWidth(0.5);
//            $lowPoint = $this->y-55;
            $page->drawRectangle($this->_leftRectPad, $this->y, $this->_leftRectPad + 215, $lowPoint);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page);

            $this->y -= 10;

            $tmpY = $this->y;
            $this->y = $lowPoint;

            foreach ($data as $value) {
                $value = trim($value);
                if ($value !== '') {
                    $value = str_replace("\r", "", $value);
                    $page->drawText($value, $this->_leftTextPad, $tmpY, 'UTF-8');
                    $tmpY -= 7;
                }
            }

//            if( $this->y > $tmpY )
//                    $this->y = $tmpY;
        } else {

            $this->y = $lowPoint;

        }
    }

    /**
     * Quote reneral remark
     *
     * @param  $page
     */
    protected function insertGeneralRemark(&$page)
    {
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y - 25);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);

        $this->y -= 10;

        $qquoteadvRemark = Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/qquoteadv_remark', $this->_quoteadv->getStoreId());
        if ($qquoteadvRemark) {
            $qquoteadvRemark = strip_tags($qquoteadvRemark);
            $qquoteadvRemark = wordwrap($qquoteadvRemark, 165, "\n");
            $data = explode("\n", $qquoteadvRemark);
            foreach ($data as $value) {
                $value = trim($value);
                if ($value !== '') {
                    $value = str_replace("\r", "", $value);
                    $page->drawText($value, $this->_leftTextPad, $this->y, 'UTF-8');
                    $this->y -= 7;
                }
            }
        }
    }

    /**
     * Get SKU with support for configurables and bundles
     *
     * @param $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }


    /**
     * Get attribute options array
     * @param object $product
     * @param string $attribute
     * @return array
     */
    public function getOption($product, $attribute)
    {
        $superAttribute = array();
        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute));
        }
        return $superAttribute;
    }

    /**
     * Function that returns the options for a simple or virtual product
     *
     * @param $product
     * @param $superAttribute
     * @return array
     */
    protected function retrieveOptions($product, $superAttribute)
    {
        $attr = array();

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            if ($superAttribute) {
                foreach ($superAttribute as $option => $value) {
                    if (!empty($value)) {
                        $attr[] = $option;
                        $value = explode(PHP_EOL, $value);
                        foreach($value as $multipleValue){
                            $attr[] = '   ' . $multipleValue;
                        }
                    }
                }
            }
        }

        return $attr;
    }

    /**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->addNewPage(); //$this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 7 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        $this->setFontStyle($page, $fontSize, $fontStyle);
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $this->setFontStyle($page, $fontSize, $fontStyle);
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        if (is_array($part)) {
                            $part_array = $part;
                            $part = $part_array['text'];
                            $this->setFontStyle($page, $part_array['font_size'], $part_array['font']);
                        }

                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                if ($maxHeight > $this->imgHeight) {
                    $this->y -= $maxHeight;
                } else {
                    $this->y -= $this->imgHeight;
                }
            }
        }

        return $page;
    }

    /**
     * Set Font Style for text
     *
     * @param object $page Zend_Pdf_Page
     * @param integer $fontSize
     * @param string $fontStyle
     */
    public function setFontStyle($page, $fontSize, $fontStyle)
    {
        switch ($fontStyle) {
            case 'bold':
                $this->_setFontBold($page, $fontSize);
                break;
            case 'italic':
                $this->_setFontItalic($page, $fontSize);
                break;
            default:
                $this->_setFontRegular($page, $fontSize);
                break;
        }
    }

    /**
     * Reformat Address for PDF
     *
     * @param array $shipTo
     * @return array
     */
    public function reformatAddress($shipTo)
    {
        // Combine Country and Region
        if (!empty($shipTo['region'])) {
            $shipTo['region'] = $shipTo['region'] . ', ' . $shipTo['country'];
            unset($shipTo['country']);
        } else {
            unset($shipTo['region']);
        }
        // Combine name and Company
        if (!empty($shipTo['company'])) {
            $shipTo['name'] = $shipTo['name'] . ', ' . $shipTo['company'];
            unset($shipTo['company']);
        }
        // Remove telephone info
        unset($shipTo['telephone']);

        return $shipTo;
    }

}
