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

class Ophirah_Qquoteadv_Model_Qqadvproduct extends Mage_Core_Model_Abstract
{
    public $_default_thumb_size = 75;
    public $imageType = 'thumbnail';
    public $imgSize = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvproduct');
    }

    /**
     * Delete product from quote
     * @param integer $id id
     */
    public function deleteQuote($id)
    {
        $this->setId($id)->delete();
        return $this;
    }

    /**
     * Get product for the particular quote
     * @param integer $quoteId
     * @return object product information
     */
    public function getQuoteProduct($quoteId)
    {
        return $this->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
    }

    /**
     * Load product from database by productId
     * or pass product if instance of
     * Mage_Catalog_Model_Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean|\Mage_Catalog_Model_Product
     */
    public function loadProduct($product)
    {
        // Get Product from Database
        if (is_string($product)) {
            $product = (int)$product;
        }

        if (is_int($product)) {
            return Mage::getModel('catalog/product')->load($product);
        } elseif ($product instanceof Mage_Catalog_Model_Product) {
            return $product;
        } else {
            $message = 'Could not determine product';
            Mage::log('Message: ' .$message, null, 'c2q.log', true);
            return false;
        }
    }

    /**
     * Get the quote item cost price of a given quote product
     *
     * @param $product
     * @param $quoteProductId
     * @param null $requestId
     * @return bool|int
     */
    public function getQuoteItemCost($product, $quoteProductId, $requestId = null)
    {
        // Get Product
        $quoteProduct = $this->loadProduct($product);

        //check for overwrite
        if(isset($requestId) && !empty($requestId)){
            $quoteadvProductRequest = Mage::getModel('qquoteadv/requestitem')->load($requestId);
            $quoteadvProductRequestCostPrice = $quoteadvProductRequest->getCostPrice();

            if(isset($quoteadvProductRequestCostPrice) && !empty($quoteadvProductRequestCostPrice)){
                //return overwrite
                return $quoteadvProductRequestCostPrice;
            }
        }

        if ($quoteProduct) {
            // Composite Product
            if ($quoteProduct->isComposite()) {
                $quoteChildren = $this->collectQuoteItemChildren($quoteProduct, $quoteProductId);
                $childCost = 0;
                foreach ($quoteChildren as $child) {
                    $qty = ($child->getData('quote_item_qty') > 0) ? $child->getData('quote_item_qty') : 1;

                    //this could be better
                    if(is_array($qty)){
                        //bad way of getting the first value of the array
                        foreach($qty as $qtyValue){
                            $qty = $qtyValue;
                            break;
                        }
                    }

                    $childCost += $child->getCost() * $qty;
                }
                return $childCost;
            } else {
                //check for tier cost
                if(isset($requestId) && !empty($requestId)) {
                    if (Mage::helper('qquoteadv/licensechecks')->isAllowedTierCost()) {
                        $tierCostPrice = Mage::getModel('qquoteadv/tiercost')->getTierCost($requestId);

                        if (isset($tierCostPrice) && !empty($tierCostPrice)) {
                            return $tierCostPrice;
                        }
                    }
                }

                return $quoteProduct->getCost();
            }
        }

        return false;
    }


    /**
     * Add children to Parent Item
     *
     * @param integer / Mage_Catalog_Model_Product              $product
     * @param integer / Ophirah_Qquoteadv_Model_Qqadvproduct    $quoteProductId
     * @return Mage_Catalog_Model_Product
     */
    public function getQuoteItemChildren($product, $quoteProductId)
    {
        // Get Product
        $quoteProduct = $this->loadProduct($product);

        if ($quoteProduct && $quoteProductId) {
            // Composite Product
            if ($quoteProduct->isComposite()) {
                $quoteChildProduct = $this->collectQuoteItemChildren($quoteProduct, $quoteProductId);
            }

            if (isset($quoteChildProduct) && count($quoteChildProduct) > 0) {
                $quoteProduct->setChildren($quoteChildProduct);
            }

            return $quoteProduct;
        }

        return false;
    }

    /**
     * Retrieve childitems for Parent Item
     *
     * @param integer / Mage_Catalog_Model_Product              $product
     * @param integer / Ophirah_Qquoteadv_Model_Qqadvproduct    $quoteProductId
     * @return Mage_Catalog_Model_Product
     */
    public function collectQuoteItemChildren($product, $quoteProductId)
    {
        $quoteChildProduct = array();

        // Get Product from Database
        $quoteProduct = $this->loadProduct($product);

        // Configurable Product
        if ($quoteProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $childProduct = $this->getConfChildProduct($quoteProductId);
            // Create link with parent Quote Item
            $childProduct->setParentQuoteItemId($quoteProductId);

            $quoteChildProduct[] = $childProduct;
        }

        // Bundle Product
        if ($quoteProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $buyRequest = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                    ->load($quoteProductId)
                    ->getData('attribute')
            );

            $quoteChildProduct = $this->getBundleOptionProducts($quoteProduct, $buyRequest, $quoteProductId);
        }

        return $quoteChildProduct;
    }

    /**
     * Retrieve Custom Options from
     * Product or ProductId
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @return \Varien_Object|boolean
     */
    public function getCustomOptionsArray($product)
    {
        if (is_int($product)) {
            $product = Mage::getModel('catalog/product')->load($productId);
        }

        if (is_object($product) && $product instanceof Mage_Catalog_Model_Product && $product->getOptions()){
            // collect Product Options
            $prodOptions = new Varien_Object();
            foreach ($product->getOptions() as $option) {
                $valuesArray = array();
                $optionTypeId = $option->getOptionId();
                $values = $option->getValuesCollection();
                if ($values) {
                    foreach ($values->getData() as $value) {
                        $valuesArray[$value['option_type_id']] = $value;
                    }
                }

                $prodOptions->setData($optionTypeId, $valuesArray);
            }
            return $prodOptions;

        }

        return false;
    }

    /**
     * Retrieve product image for Quote Product
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $product
     * @param string / int $thumbsize
     * @return string product image url
     */
    public function getItemPicture($product = null, $thumbsize = null, $cache = true, $imageType = null)
    {

        // Make sure thumbnail size is an integer
        if (!$thumbsize == null && !is_int($thumbsize)) {
            $thumbsize = (int)$thumbsize;
        }

        // Load product if none is given
        if ($product == null) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
        }

        // give thumbnail default size if none is given
        if ($thumbsize == null) {
            $thumbsize = $this->_default_thumb_size;
        }

        // Get right product to load image from
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $imageProduct = $this->getConfChildProduct($this->getId());
        } else {
            $imageProduct = $product;
        }

        // Load product image
        if ($imageType == null) {
            $imageType = $this->imageType;
        }

        if ($cache === true) {
            $image = Mage::helper('catalog/image')->init($imageProduct, $imageType);

            // Resize image if needed
            if (!$thumbsize === false) {
                $image->resize($thumbsize);
            }
        } else {
            //GET NON-CHACHED IMAGE URL
            $image =Mage::getModel('catalog/product_media_config')->getMediaUrl($imageProduct->getImage());

            //image url support
            try{
                $imageData = getimagesize($image);
                if (!is_array($imageData)) {
                    // url has no image

                    try{
                        $imageData = getimagesize($imageProduct->getImage());
                        if (is_array($imageData)) {
                            // url has image
                            $image = $imageProduct->getImage();
                        }
                    } catch (Exception $e) {
                        // no image available, do nothing
                        Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                    }
                }
            } catch (Exception $e) {
                // url has no image
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);

                try{
                    $imageData = getimagesize($imageProduct->getImage());
                    if (is_array($imageData)) {
                        // url has image
                        $image = $imageProduct->getImage();
                    }
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                    // no image available, do nothing
                }

            }

            // Resize image if needed
            if (!$thumbsize === false) {

                // get picture dimensions
                $cacheImage = Mage::helper('catalog/image')->init($imageProduct, $imageType);
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($cacheImage, $thumbsize);
                $this->imgSize = new Varien_Object();
                $this->imgSize->setData($newDim);
            }

        }

        return (string)$image;
    }

    /**
     * Check if an products has an image
     *
     * @param $product
     * @return bool
     */
    public function hasImage($product)
    {
        return $product->getImage() !== 'no_selection';
    }

    /**
     *  For configurable products,
     *  get configured simple product
     * @param integer $productQuoteId
     * @return childproductId
     */
    public function getConfChildProduct($productQuoteId)
    {
        $quote_prod = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                ->load($productQuoteId)
                ->getData('attribute')
        );

        $product = Mage::getModel('catalog/product')->load($quote_prod['product']);
        $childProduct = Mage::getModel('catalog/product_type_configurable')
            ->getProductByAttributes($quote_prod['super_attribute'], $product);

        return Mage::getModel('catalog/product')->load($childProduct->getId());
    }

    /**
     *  For bundeld products,
     *  get bundle child products
     * @param integer $productQuoteId
     * @return childproductIds
     */
    public function getBundleChildProduct($productQuoteId)
    {
        $quote_prod = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                ->load($productQuoteId)
                ->getData('attribute')
        );
        $product = Mage::getModel('catalog/product')->load($quote_prod['product']);
        $childProductArray = $product->getTypeInstance(true)->getChildrenIds($product->getId(), false);

        return $childProductArray;
    }

    /**
     * @param   object      // Mage_Catalog_Model_Product
     * @param   array       // Buy Request Bundle Parent Item
     * @param   integer     // Quote Product Id
     * @return  object      // Mage_Catalog_Model_Product
     */
    public function getBundleOptionProducts($product, $buyRequest, $quoteProductId = NULL)
    {
        $bundleOptions = Mage::getModel('qquoteadv/bundle')->getBundleOptionsSelection($product, $buyRequest);
        foreach ($bundleOptions as $option) {
            foreach ($option['value'] as $optionItem) {
                $childId = $optionItem['id'];
                $qty = $optionItem['qty'];
                $childProd = Mage::getModel('catalog/product')->load($childId);
                // Create link with parent Quote Item
                if ($quoteProductId != NULL) {
                    $childProd->setParentQuoteItemId($quoteProductId);
                }
                $childProd->setQuoteItemQty($qty);
                $quoteChildProduct[] = $childProd;

            }
        }

        return $quoteChildProduct;
    }

    /**
     * Add product for the particular quote to qquote_product table
     * @param array $params product information to be added
     *
     */
    public function addProduct($params)
    {

        $checkQty = $this->checkQuantities($params['product_id'], $params['qty']);
        if ($checkQty->getHasError()) {
            return $checkQty;
        }

        $this->setData($params)
            ->save();

        return $this;
    }

    /**
     * Update product if the product is already added to the table by the customer for the particular session
     * @param integer $id row id to be updated
     * @param array $params array of field(s) to be updated
     */
    public function updateProduct($id, $params)
    {
        $pid = $this->load($id)->getData('product_id');

        $checkQty = $this->checkQuantities($pid, $params['qty']);
        if ($checkQty->getHasError()) {
            return $checkQty;
        }


        $this->addData($params)
            ->setId($id)
            ->save();

        return $this;
    }

    /**
     * Function to update a quote product with params of the quote request form
     *
     * @param $params
     * @return $this
     */
    public function updateQuoteProduct($params)
    {
        foreach ($params as $key => $arr) {
            $item = Mage::getModel('qquoteadv/qqadvproduct')->load($arr['id']);
            try {
                $item->setQty($arr['qty']);
                if ($arr['client_request']) {
                    $item->setClientRequest($arr['client_request']);
                }
                if (array_key_exists('attribute', $arr)) {
                    $item->setAttribute($arr['attribute']);
                }
                $item->save();
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        return $this;
    }

    /**
     * Update Product Qty
     * Used for tier selection
     *
     * @param int $itemId
     * @param int $itemQty
     * @return bool
     */
    public function updateProductQty($itemId, $itemQty)
    {
        if (!(int)$itemId && !(int)$itemQty) {
            return false;
        }
        $item = Mage::getModel('qquoteadv/qqadvproduct')->load($itemId);
        if ($item && $itemQty > 0) {
            try {
                $attribute = unserialize($item->getAttribute());
                $attribute['qty'] = (string)$itemQty;
                $item->setAttribute(serialize($attribute));
                $item->setQty((string)$itemQty);
                $item->save();
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Function that returns an array of quote products id's that are in a given quote
     *
     * @param $quoteId
     * @return array
     */
    public function getIdsByQuoteId($quoteId)
    {
        $ids = array();
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);

        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }

    /**
     * Function that checks if a given product is used in a given quote
     *
     * @param $productId
     * @param $quoteId
     * @return bool
     */
    public function hasProductId($productId,$quoteId)
    {
        foreach ($this->getCollection()->addFieldToFilter('quote_id',$quoteId) as $item) {
            if ($item->getProductId() == $productId) {
                return true;
            }
        }

        return false;
    }


    /**
     * If the product ID is already on a quote, comparison is done based
     * on serialized attributes to check if the options are also the same.
     *
     * @param $product
     * @param $quoteId
     * @return bool
     */
    public function getIdByQuoteAndProduct($product, $quoteId)
    {
        foreach ($this->getCollection()->addFieldToFilter('quote_id',$quoteId) as $item) {
            if ($item->getProductId() == $product->getProductId()) {
                //check if the options of the product are the same.
                $superAttribute = $product->getProduct()->getTypeInstance(true)->getOrderOptions($product->getProduct());

                $optionalAttrib = '';
                if (isset($superAttribute['info_buyRequest'])) {
                    if (isset($superAttribute['info_buyRequest']['uenc'])) {
                        unset($superAttribute['info_buyRequest']['uenc']);
                    }

                    $superAttribute['info_buyRequest']['product'] = $item->getData('product_id');
                    $superAttribute['info_buyRequest']['qty'] = $item->getQty();

                    $optionalAttrib = serialize($superAttribute['info_buyRequest']);
                }

                $itemAttribute = $item->getAttribute();
                if(isset($itemAttribute)){
                    if(isset($optionalAttrib)){
                        if($itemAttribute == $optionalAttrib){
                            //this product has the same options
                            return $item->getId();
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Maps checkQuantities to the checkQuantities of the qquoteadv helper
     *
     * @param $id
     * @param $qty
     * @return mixed
     */
    public function checkQuantities($id, $qty)
    {
        return Mage::helper('qquoteadv')->checkQuantities($id, $qty);
    }

    /**
     * Maps checkQtyIncrements to the checkQtyIncrements of the qquoteadv helper
     *
     * @param $id
     * @param $qty
     * @return mixed
     */
    public function checkQtyIncrements($id, $qty)
    {
        return Mage::helper('qquoteadv')->checkQtyIncrements($id, $qty);
    }

    /**
     * Create Array with quoted products and custom prices
     *
     * @param   $quoteId -> Quote Id
     * @return  Array with products and custom prices
     */
    public function getQuoteCustomPrices($quoteId)
    {

        // Get Custom Quote product price data from database
        $quoteItems = Mage::getModel('qquoteadv/requestitem')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);

        // Create Array with custom quote prices, per tier
        $quoteProductPrices = array();
        foreach ($quoteItems as $quoteItem) {
            $quoteProductPrices[$quoteItem->getData('quoteadv_product_id')][$quoteItem->getData('request_qty')] = $quoteItem->getData();
        }


        // Get Custom Quote product data from database
        $quoteProducts = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);

        foreach ($quoteProducts as $quoteProduct) {

            // Get Attribute from product
            $attribute = unserialize($quoteProduct->getData('attribute'));

            $quote_prod = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                ->load($quoteProduct->getData('id'))
                ->getData('attribute')
            );

            $product = Mage::getModel('catalog/product')->load($quote_prod['product']);

            // If product is configurable, super_attribute is set
            if (isset($attribute['super_attribute']) && $product->isConfigurable()) {
                $childProd = $this->getConfChildProduct($quoteProduct->getData('id'));

                $childInfoArray = array('entity_id', 'sku', 'allowed_to_quotemode');

                foreach ($childInfoArray as $prodData) {
                    $childInfo[$prodData] = $childProd->getData($prodData);
                }

                $quoteProduct->setData('child_item', $childInfo);
            }

            // If product is bundle, bundle_option is set
            if (isset($attribute['bundle_option']) && $product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                // Get childproduct Id's
                $childProdIds = $this->getBundleChildProduct($quoteProduct->getData('id'));

                // get original bundle price
                $bundlePrice = Mage::getModel('catalog/product')->load($quoteProduct->getData('product_id'))->getPrice();
                $priceType = Mage::getModel('catalog/product')->load($quoteProduct->getData('product_id'))->getPriceType();

                //init vars
                $bundleInfo = array();
                $childPricesArray = array();
                $childCostsArray = array();

                $prodPrices = array();
                $prodCosts = array();

                if($priceType == 0){
                    //Item is bundle with dynamic pricing

                    // create array with child id's and original child price and cost
                    foreach ($attribute['bundle_option'] as $key => $bindeOptionIdA) {

                        if(!is_array($bindeOptionIdA)){
                            $bindeOptionIds[] = $bindeOptionIdA;
                        } else {
                            $bindeOptionIds = $bindeOptionIdA;
                        }

                        foreach ($bindeOptionIds as $bindeOptionId) {
                            $childId = Mage::getModel('bundle/selection')->load($bindeOptionId)->getData('product_id');
                            $prod = Mage::getModel('catalog/product')->load($childId);

                            //if qty is more than one, check for tier pricings
                            if(isset($attribute['bundle_option_qty']) && isset($attribute['bundle_option_qty'][$key])){
                                //this could be better
                                if(is_array($attribute['bundle_option_qty'][$key])){
                                    //bad way of getting the first value of the array
                                    foreach($attribute['bundle_option_qty'][$key] as $qtyValue){
                                        $attribute['bundle_option_qty'][$key] = $qtyValue;
                                        break;
                                    }
                                }

                                if(isset($attribute['bundle_option_qty']) && $attribute['bundle_option_qty'][$key] > 1){
                                    $tierPirces = $prod->getTierPrice();
                                    if(isset($tierPirces) && !empty($tierPirces)){
                                        //select the corect tier price from the array
                                        foreach ($tierPirces as $tierPirce) {
                                            if($tierPirce['price_qty'] == $attribute['bundle_option_qty'][$key]){
                                                $prodPrice = $tierPirce['price'] * $attribute['bundle_option_qty'][$key];
                                                break;
                                            } else {
                                                $prodPrice = $prod->getFinalPrice() * $attribute['bundle_option_qty'][$key];
                                            }
                                        }
                                    } else {
                                        $prodPrice = $prod->getFinalPrice() * $attribute['bundle_option_qty'][$key];
                                    }
                                    $prodCost = $prod->getCost() * $attribute['bundle_option_qty'][$key];
                                } else {
                                    //don't check for tier pricing
                                    $prodPrice = $prod->getFinalPrice();
                                    $prodCost = $prod->getCost();
                                }
                            } else {
                                //don't check for tier pricing
                                $prodPrice = $prod->getFinalPrice();
                                $prodCost = $prod->getCost();
                            }

                            //fallback to product price if product cost is not available.
                            if($prodCost == null){
                                $prodCost = $prodPrice;
                            }
                            $prodPrices[$childId] = $prodPrice;
                            $prodCosts[$childId] = $prodCost;
                        }
                    }

                    $quoteProductId = $quoteProduct->getData('id');
                    if(!isset($childPricesArray[$quoteProductId]) || empty($childPricesArray[$quoteProductId])){
                        $childPricesArray[$quoteProductId] = $prodPrices;
                        $childCostsArray[$quoteProductId] = $prodCosts;
                    } else {
                        $childPricesArray[$quoteProductId] = ($prodPrices + $childPricesArray[$quoteProductId]);
                        $childCostsArray[$quoteProductId] = ($prodCosts + $childCostsArray[$quoteProductId]);
                    }

                } else {
                    //Item is bundle with fixed pricing

                    // create array with child id's and original child price and cost
                    foreach ($childProdIds as $childProdId) {
                        $prodPrices = array();
                        $prodCosts = array();

                        foreach ($childProdId as $childId) {
                            $prod = Mage::getModel('catalog/product')->load($childId);
                            $prodPrice = $prod->getPrice();
                            $prodCost = $prod->getCost();
                            $prodPrices[$childId] = $prodPrice;
                            $prodCosts[$childId] = $prodCost;
                        }

                        $quoteProductId = $quoteProduct->getData('id');
                        if(!isset($childPricesArray[$quoteProductId]) || empty($childPricesArray[$quoteProductId])){
                            $childPricesArray[$quoteProductId] = $prodPrices;
                            $childCostsArray[$quoteProductId] = $prodCosts;
                        } else {
                            $childPricesArray[$quoteProductId] = ($prodPrices + $childPricesArray[$quoteProductId]);
                            $childCostsArray[$quoteProductId] = ($prodCosts + $childCostsArray[$quoteProductId]);
                        }

                    }
                }

                $bundleInfo['bundle_orgprice'] = $bundlePrice;
                $bundleInfo['child_orgprices'] = $childPricesArray;
                $bundleInfo['child_costs'] = $childCostsArray;
                // set info in object
                $quoteProduct->setData('bundle_info', $bundleInfo);
            }

            // set custom price
            $customBasePrice = array();
            $customCurPrice = array();
            $customCostPrice = array();
            $requestIds = array();

            if(!empty($quoteProductPrices)) {
                $quoteProductPricesProductIds = $quoteProductPrices[$quoteProduct->getData('id')];

                foreach ($quoteProductPricesProductIds as $key => $value) {
                    $customBasePrice[$key] = $value['owner_base_price'];
                    $customCurPrice[$key] = $value['owner_cur_price'];
                    $customCostPrice[$key] = $value['cost_price'];
                    $requestIds[$key] = $value['request_id'];
                }
            }

            $quoteProduct->setData('custom_base_price', $customBasePrice);
            $quoteProduct->setData('custom_cur_price', $customCurPrice);
            $quoteProduct->setData('custom_cost_price', $customCostPrice);
            $quoteProduct->setData('request_ids', $requestIds);
        }

        return $quoteProducts;
    }

    /**
     * Set custom prices to item object
     *
     * @param   $quoteCustomPrices  -> Array with custom prices
     * @param   $quoteId            -> Quote Item
     * @param   $optionCount        -> Counter for current product option number
     * @return  Quote item object with custom prices
     */
    public function getCustomPriceCheck($quoteCustomPrices, $item, $optionCount = null)
    {

        // Get product id the current item belongs to
        if ($item->getBuyRequest()->getData('product')) {
            $buyRequest = $item->getBuyRequest();
            $product_id = $buyRequest->getData('product');
        } else {
            $product_id = null;
        }

        // Check if current item has a custom price.
        foreach ($quoteCustomPrices as $requestId => $quoteCustomPrice) {

            $attribute = unserialize($quoteCustomPrice->getData('attribute'));

            // Basic Compare
            $compareQuote = $quoteCustomPrice->getData('product_id');
            $compareItem = $item->getData('product_id');

            // For products with options and parent-child relations
            // Dynamic bundle options can have different object with the same product_id
            if (isset($product_id) && $product_id == $quoteCustomPrice->getData('product_id')) {

                $requestIdsCost = $quoteCustomPrice->getData('request_ids');
                $requestIdCost = $requestIdsCost[$quoteCustomPrice->getData('qty')];
                // Item Costprice
                $itemCost = $this->getQuoteItemCost($item->getProduct(), $quoteCustomPrice->getData('id'), $requestIdCost);

                // Custom Options
                if (isset($buyRequest['options'])) {
                    if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                        $compareQuote = $attribute['options'];
                        $compareItem = $buyRequest['options'];
                    } else {
                        if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {
                            $compareQuote = $attribute['options'];
                            $compareItem = $buyRequest['options'];
                        }
                    }
                }


                // Configurable products
                if (isset($buyRequest['super_attribute'])) {
                    if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $compareQuote = $attribute['super_attribute'];
                        $compareItem = $buyRequest['super_attribute'];
                    }
                }

                // Bundled Products
                if (isset($buyRequest['bundle_option'])) {

                    if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {

                        $bundleInfo = $quoteCustomPrice->getData('bundle_info');

                        $requestHandeled = Mage::registry('requests_handeled');
                        //if there is no bundle info, or this is the wrong bundle, continue.
                        if($bundleInfo == '' || !isset($bundleInfo['child_orgprices'][$requestId]) || in_array($requestId, $requestHandeled)){
                            continue;
                        } else {
                            $requestHandeled[] = $requestId;
                            Mage::unregister('requests_handeled');
                            Mage::register('requests_handeled', $requestHandeled);
                        }

                        /*
                         * Item is bundle
                         */
                        $productPriceType = Mage::getModel('catalog/product')->load($item->getData('product_id'))->getPriceType();

                        if($productPriceType == 0){
                            //dynamic bundle price
                            $price = 0;
                        } else {
                            //fixed bundle price
                            $orgPrice = $item->getProduct()->getPrice();
                            $price = $item->getProduct()->getPrice();

                            //support for specialPrice
                            $specialPrice = $item->getProduct()->getSpecialPrice();

                            //we cant use this here because it returns our own special price not the default special price
                            //$priceFinal = $item->getProduct()->getFinalPrice();

                            if($specialPrice !== null){
                                $specialPriceFromDate = $item->getProduct()->getSpecialFromDate();
                                $specialPriceToDate = $item->getProduct()->getSpecialToDate();

                                if (!is_null($specialPrice) && $specialPrice != false) {
                                    if (Mage::app()->getLocale()->isStoreDateInInterval(null, $specialPriceFromDate, $specialPriceToDate)) {
                                        $price = min($price, $specialPrice);
                                    }
                                }
                            }

                            //support for tier price
                            $tierPrice = $item->getProduct()->getTierPrice($item->getQty());
                            if($tierPrice !== null){
                                $tierPrice = ((1 - ($tierPrice / 100)) * $orgPrice);
                                $price = min($price, $tierPrice);
                            }

                            //support for group price
                            $groupPrice = $item->getProduct()->getGroupPrice($item->getProduct());
                            if($groupPrice !== null){
                                $groupPrice = ((1 - ($groupPrice / 100)) * $orgPrice);
                                $price = min($price, $groupPrice);
                            }
                        }

                        if (isset($bundleInfo['child_orgprices'])) {
                            foreach ($bundleInfo['child_orgprices'] as $child_prices) {
                                if(isset($child_prices) && !empty($child_prices) && is_array($child_prices)){
                                    foreach ($child_prices as $id => $child_price) {
                                        foreach($attribute['bundle_option'] as $optionId => $selectionId ){
                                            if(is_array($selectionId)){
                                                foreach($selectionId as $childOptionId =>$childSelectionId){
                                                    $price = $this->_setBundleChildPrice($id,  $productPriceType, $child_price, $price, $childSelectionId, $attribute, $optionId, $childOptionId);
                                                }
                                            }else{
                                                $price = $this->_setBundleChildPrice($id, $productPriceType, $child_price, $price, $selectionId, $attribute, $optionId);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $cost = $item->getData('base_cost');
                        if (isset($bundleInfo['child_costs'])) {
                            foreach ($bundleInfo['child_costs'] as $child_costs) {
                                if(isset($child_costs) && !empty($child_costs) && is_array($child_costs)){
                                    foreach ($child_costs as $id => $child_cost) {
                                        foreach($attribute['bundle_option'] as $optionId => $selectionId ){
                                            if(is_array($selectionId)){
                                                foreach($selectionId as $childOptionId =>$childSelectionId){
                                                    $cost = $this->_setBundleChildCostPrice($childSelectionId, $id, $attribute, $optionId, $child_cost, $cost, $childOptionId);
                                                }
                                            }else{
                                                $cost = $this->_setBundleChildCostPrice($selectionId, $id, $attribute, $optionId, $child_cost, $cost);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $item->setData('quote_org_price', $price);
                        $item->setData('quote_item_cost', $cost);

                        $customBasePrice = $quoteCustomPrice->getData('custom_base_price');
                        $customCurPrice = $quoteCustomPrice->getData('custom_cur_price');

                        $item->setData('qty', $quoteCustomPrice->getData('qty'));
                        $item->setData('custom_base_price', $customBasePrice[$quoteCustomPrice->getData('qty')]);
                        $item->setData('custom_cur_price', $customCurPrice[$quoteCustomPrice->getData('qty')]);

                        return $item;

                    }
                }


                // Grouped products
                if (isset($buyRequest['super_product_config']) || isset($attribute['super_product_config'])) {
                    $compareQuote = !empty($attribute['super_product_config']) ? $attribute['super_product_config'] : $compareQuote;
                    $compareItem = !empty($buyRequest['super_product_config']) ? $buyRequest['super_product_config'] : $buyRequest;
                }

            }

            $downloadableCheck = Mage::getModel('qquoteadv/qqadvproductdownloadable')
                ->exists($quoteCustomPrice, $item->getBuyRequest()->getLinks());

            if ($compareQuote == $compareItem && !$downloadableCheck) {

                $customBasePrice = $quoteCustomPrice->getData('custom_base_price');
                $customCurPrice = $quoteCustomPrice->getData('custom_cur_price');

                $item->setData('qty', $quoteCustomPrice->getData('qty'));

                //$item->setData('custom_base_price', $customBasePrice[$quoteCustomPrice->getData('qty')]);
                if(isset($customBasePrice[$quoteCustomPrice->getData('qty')]) && !empty($customBasePrice[$quoteCustomPrice->getData('qty')])){
                    $item->setData('custom_base_price', $customBasePrice[$quoteCustomPrice->getData('qty')]);
                } else {
                    $item->setData('custom_base_price', $customBasePrice['1']);
                }

                //$item->setData('custom_cur_price', $customCurPrice[$quoteCustomPrice->getData('qty')]);
                if(isset($customCurPrice[$quoteCustomPrice->getData('qty')]) && !empty($customCurPrice[$quoteCustomPrice->getData('qty')])){
                    $item->setData('custom_cur_price', $customCurPrice[$quoteCustomPrice->getData('qty')]);
                } else {
                    $item->setData('custom_cur_price', $customCurPrice['1']);
                }

                //set item cost
                if(isset($itemCost)){
                    $item->setData('quote_item_cost', $itemCost);
                }

            }

        }

        return $item;
    }


    /**
     * Gets the amount of selected options
     *
     * @param $buyRequest -> the products buy request
     * @return int -> number of selected options
     */
    public function getCountMax($buyRequest)
    {

        $return = 0;

        // array of possible options in buyRequest
        $optionAttributes = array("options", "super_attribute", "bundle_option");

        foreach ($optionAttributes as $optionAttribute) {
            if ($buyRequest->getData($optionAttribute)) {
                $return = count($buyRequest->getData($optionAttribute));
            }
        }

        return $return;

    }

    /**
     * Sets the price of a child of the bundle
     * @param $id
     * @param $productPriceType
     * @param $child_price
     * @param $price
     * @param $selectionId
     * @param $attribute
     * @param $optionId
     * @param null $optionChildId
     * @return array
     * @internal param $childId
     */
    protected function _setBundleChildPrice($id, $productPriceType, $child_price, $price, $selectionId, $attribute, $optionId, $optionChildId = null)
    {
        $child = Mage::getModel('bundle/selection')->load($selectionId);
        $childId = $child->getData('product_id');
        if ($id == $childId) {
            if ($productPriceType == 0) {
                //dynamic bundle price
                $price += $child_price;
            } else {
                //fixed bundle price
                $selectionChildPrice = $child->getData('selection_price_value');
                $selectionChildQty = $this->_getOptionChildQty($attribute, $optionId, $optionChildId);
                $price += ($selectionChildPrice * $selectionChildQty);
            }
        }
        return $price;
    }

    /**
     * Set the cost price of a bundled child
     * @param $selectionId
     * @param $id
     * @param $attribute
     * @param $optionId
     * @param $child_cost
     * @param $cost
     * @param null $optionChildId
     * @return array
     */
    protected function _setBundleChildCostPrice($selectionId, $id, $attribute, $optionId, $child_cost, $cost, $optionChildId = null)
    {
        $childId = Mage::getModel('bundle/selection')->load($selectionId)->getData('product_id');
        if ($id == $childId) {
            $selectionChildQty = $this->_getOptionChildQty($attribute, $optionId, $optionChildId);
            if(is_array($selectionChildQty)){
                $cost += $child_cost; // To-Do: Not accurate with selectable product in bundles.
            }else{
                $cost += ($child_cost * $selectionChildQty);
            }
        }
        return $cost;
    }

    /**
     * Gets the qty from a child option
     * @param $attribute
     * @param $optionId
     * @param $optionChildId
     * @return int
     */
    protected function _getOptionChildQty($attribute, $optionId, $optionChildId)
    {
        if (isset($attribute['bundle_option_qty'][$optionId])) {
            if ($optionChildId) {
                $selectionChildQty = $attribute['bundle_option_qty'][$optionId][$optionChildId];
            } else {
                $selectionChildQty = $attribute['bundle_option_qty'][$optionId];
            }
        } else {
            $selectionChildQty = 0;
        }
        return $selectionChildQty;
    }
}
