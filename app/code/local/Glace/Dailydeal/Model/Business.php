<?php

/**
 * Contain methods 
 * - Action
 * - Auto
 */
class Glace_Dailydeal_Model_Business extends Mage_Core_Model_Abstract
{

    /**
     * @group Action
     * Front end : increase view deal if show at Today's Deal
     */
    public static function increateFeaturedView($deal_id)
    {
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel()->load($deal_id);
        $model_session = Mage::getModel('core/session');
        $session_deal_id_views = $model_session->getData('glace_dailydeal_featured_view_array');
        $session_deal_id_views = ($session_deal_id_views == null) ? array() : $session_deal_id_views;
        
        $visitor_data = $model_session->getData('visitor_data');
        
        // Have one action : back end, admin access to deal also increase view => Back end not running
        if($visitor_data == null){
            return; 
        }

        if ($model_deal->getId()) {

            if (in_array($model_deal->getId(), $session_deal_id_views)) {
                // Customer has already viewed this deal
            } else {
                // Customer yet view this deal -> increase view
                $model_deal->setData('impression', $model_deal->getData('impression') + 1);
                $model_deal->save();

                $session_deal_id_views[] = $model_deal->getId();
                $model_session->setData('glace_dailydeal_featured_view_array', $session_deal_id_views);
            }
        }
    }

    /**
     * @group Action
     */
    public static function generalDeal($deal_scheduler_id)
    {
        $model_deal_scheduler = Glace_Dailydeal_Model_Dealscheduler::getModel()->load($deal_scheduler_id);
        $model_product_scheduler = Glace_Dailydeal_Model_Dealschedulerproduct::getModel();
        $model_deal = Glace_Dailydeal_Model_Dailydeal::getModel();

        $products = $model_product_scheduler->getProductOptionArray($deal_scheduler_id);
        $products_sort = $model_product_scheduler->sortProductOptionArray($products, $model_deal_scheduler->getData('generate_type'));

        $theads = $model_deal_scheduler->getData('number_deal');

        $count_generate_deal = 0;
        $now_time = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $to_time = Glace_Dailydeal_Helper_Toolasiaconnect::increaseTime($now_time, 5, $model_deal_scheduler->getData('number_day'), 'Y-m-d H:i:s');

        if ($model_deal_scheduler->getStatusTime() != Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING) {
            // Not Running, not active
            return $count_generate_deal;
        }

        // Default value
        for ($i = 0; $i < $theads; $i++) {
            $thread_start_date_time[$i] = $model_deal->getLimitStartDateTime($deal_scheduler_id, $i);
            $thread_number_product_error[$i] = 0;
            if (strtotime($thread_start_date_time[$i]) >= strtotime($to_time)) {
                $thread_have_time[$i] = false;  // Stop thread
            } else {
                $thread_have_time[$i] = true;
            }
        }
        $number_product = count($products);
        
        while (in_array(true, $thread_have_time) && $number_product > 0) {   // Have time and product
            for ($i = 0; $i < $theads; $i++) {   // Muity Thread Deal
                if (!$thread_have_time[$i]) {
                    continue;
                }

                if ((strtotime($thread_start_date_time[$i]) < strtotime($to_time))) {
                    list ($success, $end_date_time) = $model_deal_scheduler->generalDeal(array_shift($products_sort), $i, $thread_start_date_time[$i]);
                }

                if ($success) {
                    $count_generate_deal++;
                    $thread_start_date_time[$i] = $end_date_time;
                    $thread_number_product_error[$i] = 0;
                } else {
                    $thread_number_product_error[$i]++;
                }

                if (strtotime($thread_start_date_time[$i]) >= strtotime($to_time)) {
                    $thread_have_time[$i] = false;  // Stop thread because thread not have time
                }

                if ($number_product <= $thread_number_product_error[$i]) {
                    $thread_have_time[$i] = false;  // Stop thread because thread not have product
                }

                if (empty($products_sort)) {
                    $products_sort = $model_product_scheduler->sortProductOptionArray($products, $model_deal_scheduler->getData('generate_type'));
                }

                if (!$success) {
                    $i--;   // current thread run again, choice other product because product is generated error,
                }

                // Limit number of deal because action generate in small time, server is not die.
                if ($count_generate_deal == Glace_Dailydeal_Model_Status :: DEAL_SCHEDULER_GENERATE_LIMIT_AMOUNT) {
                    return $count_generate_deal;
                }
            }
        }

        return $count_generate_deal;
    }

    /**
     * @group Action
     */
    public static function sendMailAdminNotification()
    {
        $flag_send_mail_on = Glace_Dailydeal_Helper_Data::getConfigAllowSendAdminMail();
        if (!$flag_send_mail_on) {
            return;
        }

        $store_id = Mage::app()->getStore()->getId();

        $template_id = Glace_Dailydeal_Helper_Data::getConfigTemplateIdNoDeal();

        $sender = 'sales';
        $receive = Glace_Dailydeal_Helper_Data::getConfigAdminMail();

        $name = "Admin Site";
        $data['subject'] = 'Website don\'t have a deal';

        $model_translate = Mage::getSingleton('core/translate');
        $model_translate->setTranslateInline(false);
        try {
            $model_email = Mage::getModel('core/email_template');
            $model_email->sendTransactional($template_id, $sender, $receive, $name, $data, $store_id);
            $model_translate->setTranslateInline(true);

            if (!$model_email->getSentSuccess()) {
//                throw new Exception(Mage::helper('dailydeal')->__("Email can not send!"));
            }
        } catch (Exception $ex) {
//            throw new Exception(Mage::helper('dailydeal')->__("Email can not send!"));
        }
    }

    /**
     * @group Auto
     * Disable product if deal is not running and field 'disable_product_after_finish' = 1
     */
    public function autoDisableProduct()
    {
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection_deal = Glace_Dailydeal_Model_Dailydeal::getModel()->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('disable_product_after_finish', Glace_Dailydeal_Model_Status::STATUS_PRODUCT_ENABLED)
                ->addFieldToFilter('end_date_time', array('to' => $now))
                ->load();

        foreach ($collection_deal as $model_deal) {
            Mage::getSingleton('catalog/product_status')->updateProductStatus($model_deal->getData('product_id'), 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            $model_deal->setData('disable_product_after_finish', Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DONE);
            $model_deal->save();
        }
    }

    /**
     * @group Auto
     * Update field 'active' for all deal : running, queue
     */
    public static function autoUpdateDealActive()
    {
        $collection = Glace_Dailydeal_Model_Dailydeal::getModel()->getCollection()
                ->addFieldToFilter('active', array(Glace_Dailydeal_Model_Status::STATUS_TIME_QUEUED, Glace_Dailydeal_Model_Status::STATUS_TIME_RUNNING));

        foreach ($collection as $deal) {
            if($deal->getStatusTime() != $deal->getData('active')){
                $deal->save();
            }
        }
    }

    /**
     * @group Auto
     */
    public function autoGenerateDeal()
    {
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection = Glace_Dailydeal_Model_Dealscheduler::getModel()->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('start_date_time', array('to' => $now))
                ->addFieldToFilter('end_date_time', array('from' => $now));

        foreach ($collection as $model) {
            Glace_Dailydeal_Model_Business::generalDeal($model->getId());
        }
    }

    /**
     * @group Auto
     * @return int 1 : send mail successful
     */
    public static function autoSendMail()
    {
        $result = 0;
        $now = date('Y-m-d 0:0:1', Mage::getModel('core/date')->timestamp(time()));
        $tomorrow = Glace_Dailydeal_Helper_Toolasiaconnect::increaseTime($now, 5, 1, 'Y-m-d H:i:s');

        $condition = array(
            'end_date_time' => true,
            'now' => $tomorrow,
        );

        $flag_send_mail_on = Glace_Dailydeal_Helper_Data::getConfigAllowSendAdminMail();
        $flag_have_deal = Glace_Dailydeal_Model_Dailydeal::getModel()->isHaveDealRunning($condition);
        $flag_send_mail = Glace_Dailydeal_Helper_Data::getConfigSendMailAdminNotification();

        if ($flag_send_mail_on) {

            if (!$flag_have_deal && $flag_send_mail) {
                // Not deal, send mail
                Glace_Dailydeal_Model_Business::sendMailAdminNotification();
                $result = 1;
            }
        }

        return $result;
    }

}