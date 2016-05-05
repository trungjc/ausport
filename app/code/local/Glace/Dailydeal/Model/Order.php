<?php

class Glace_Dailydeal_Model_Order extends Mage_Core_Model_Abstract
{

    /**
     * @return Glace_Dailydeal_Model_Order
     */
    public static function getInstance()
    {
        return Mage::getSingleton('dailydeal/order');
    }

    /**
     * Front end when place order : mark order's product_id follow deal_id
     */
    public function markProductOfDeal($item, $deal)
    {
        $data = $item->getProductOptions();
        $data['info_buyRequest']['dailydeal_id'] = $deal->getData('dailydeal_id');
        return $data;
    }

    /**
     * Backend :when order update to 'cancel' -> deal's qty is update
     * @param Varien_Event_Observer $observer
     */
    public function cancelOrderUpdateDealQuantity($observer)
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();

        $order = $observer->getData('order');
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $product_option = unserialize($item->getData('product_options'));
            $info_buyRequest = $product_option['info_buyRequest'];
            $dailydeal_id = $info_buyRequest['dailydeal_id'];

            $model_deal->setData(array());
            $model_deal->load($dailydeal_id);

            if ($model_deal->getId() && $model_deal->getData('product_id') == $item->getData('product_id')) {
                $order_sold_qty = $item['qty_ordered'];
                $deal_sold_qty = $model_deal->getData('sold_qty');
                $model_deal->setData('sold_qty', $deal_sold_qty - $order_sold_qty);
                $model_deal->save();
            }
        }
    }
    /**
     * Backend : when order update to 'cancel' -> product is enable
     * @param Varien_Event_Observer $observer
     */
    public function cancelOrderEnableProduct($observer)
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();

        $order = $observer->getData('order');
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $product_option = unserialize($item->getData('product_options'));
            $info_buyRequest = $product_option['info_buyRequest'];
            $dailydeal_id = $info_buyRequest['dailydeal_id'];

            $model_deal->setData(array());
            $model_deal->load($dailydeal_id);
            
            if ($model_deal->getId() && $model_deal->getData('product_id') == $item->getData('product_id')) {
                Glace_Dailydeal_Model_Product::getInstance()->enableProductByDealId(array($model_deal->getId()));
            }
        }
    }
}