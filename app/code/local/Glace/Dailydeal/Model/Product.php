<?php

class Glace_Dailydeal_Model_Product extends Mage_Core_Model_Abstract
{

    /**
     * @return Glace_Dailydeal_Model_Product
     */
    public static function getInstance()
    {
        return Mage::getSingleton('dailydeal/product');
    }

    /**
     * If deal is expire and disable_product equal 1 -> disable product
     * @param array $deal_ids
     */
    public function disableProductByDealId($deal_ids = array())
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();

        foreach ($deal_ids as $id) {
            $model_deal->setData(array());
            $model_deal->load($id);

            if ($model_deal->getId()) {

                if ($model_deal->getStatusTime() == Glace_Dailydeal_Model_Status::STATUS_TIME_ENDED) {

                    if ($model_deal->getData('disable_product_after_finish') == Glace_Dailydeal_Model_Status::STATUS_PRODUCT_ENABLED) {
                        Mage::getSingleton('catalog/product_status')->updateProductStatus($model_deal->getData('product_id'), 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                        $model_deal->setData('disable_product_after_finish', Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DONE);
                        $model_deal->save();
                    }
                }
            }
        }
    }

    /**
     * If deal is expire and disable_product equal 1 -> disable product
     * @param array $deal_ids
     */
    public function enableProductByDealId($deal_ids = array())
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();

        foreach ($deal_ids as $id) {
            $model_deal->setData(array());
            $model_deal->load($id);

            if ($model_deal->getId()) {
                if ($model_deal->getData('disable_product_after_finish') == Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DONE) {
                    Mage::getSingleton('catalog/product_status')->updateProductStatus($model_deal->getData('product_id'), 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                    $model_deal->setData('disable_product_after_finish', Glace_Dailydeal_Model_Status::STATUS_PRODUCT_ENABLED);
                    $model_deal->save();
                }
            }
        }
    }

    public static function getMinPriceProductGrouped($product)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            return '';
        }
        $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $prices = array();

        foreach ($aProductIds as $ids) {
            foreach ($ids as $id) {
                $aProduct = Mage::getModel('catalog/product')->load($id);
                $prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
            }
        }

        sort($prices);
        $min_price = array_shift($prices);
        return $min_price;
    }

    public static function getMinPriceProductBundle($product)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return '';
        }

        $optionCol = $product->getTypeInstance(true)
                ->getOptionsCollection($product);
        $selectionCol = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product), $product
        );
        $optionCol->appendSelections($selectionCol);
        $price = $product->getPrice();

        foreach ($optionCol as $option) {

            if ($option->required) {
                $selections = $option->getSelections();


                $temp_price = array();
                foreach ($selections as $selection) {
                    $temp_price[] = $selection->getPrice();
                }

                $minPrice = min($temp_price);


                if ($product->getSpecialPrice() > 0) {
                    $minPrice *= $product->getSpecialPrice() / 100;
                }

                $price += round($minPrice, 2);
            }
        }
        return $price;
    }

}