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
$crmMessageTable = $installer->getTable('quoteadv_crmaddon_messages');

// Add substatus
$this->run("
    ALTER TABLE `{$crmMessageTable}` ADD `user_id` int(10) default NULL;
    ALTER TABLE `{$crmMessageTable}` ADD `customer_id` int(10) default NULL;
    ALTER TABLE `{$crmMessageTable}` ADD `send_from_frontend` tinyint(1) default '0';
    ALTER TABLE `{$crmMessageTable}` DROP FOREIGN KEY `FK_ quoteadv_crmaddon_messages_template_id`;
");

$installer->endSetup();
