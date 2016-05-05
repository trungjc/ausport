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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

$installer = $this;
$installer->startSetup();

// Insert new Column
$sql = "ALTER TABLE `{$this->getTable('quoteadv_crmaddon_messages')}` ADD `customer_notified` TINYINT(1) DEFAULT NULL AFTER `status`";
$result = $installer->getConnection()->query($sql);


// If messages exist, update status
// to 'customer notified'
$sql = "SELECT `message_id` , `customer_notified` FROM `{$this->getTable('quoteadv_crmaddon_messages')}` WHERE `customer_notified` IS NULL";
$result = $installer->getConnection()->query($sql);

if (isset($result)){
    foreach ($result as $item) {

        $update = "UPDATE {$this->getTable('quoteadv_crmaddon_messages')} SET `customer_notified`='1' WHERE (`message_id`='{$item['message_id']}')";
        $installer->run($update);
    }

}
$installer->endSetup();
