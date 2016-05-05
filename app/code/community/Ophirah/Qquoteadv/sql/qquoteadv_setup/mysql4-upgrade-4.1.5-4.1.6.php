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

$installer = $this;
$installer->startSetup();

// Extra Options table
// To be used to create a custom option field
$this->run("
  DROP TABLE IF EXISTS `{$this->getTable('quoteadv_extraoptions')}`;

  CREATE TABLE `{$this->getTable('quoteadv_extraoptions')}` (
    `option_id` int(10) unsigned NOT NULL auto_increment,
    `option_type` int(10) DEFAULT NULL,
    `value` TEXT DEFAULT NULL,
    `label` TEXT DEFAULT NULL,
    `order` int(10) DEFAULT NULL,
    `title` int(10) DEFAULT NULL,
    `status` tinyint(1) NOT NULL default '1',
    PRIMARY KEY  (`option_id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes';  
");

// Extra Email settings, Trial Hash and Salesrule
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `proposal_sent` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `created_at`;
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `no_reminder` tinyint(1) default '0' AFTER `no_expiry`;
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder` date AFTER `expiry`;
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `create_hash` VARCHAR(40) DEFAULT NULL AFTER `hash`;
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `salesrule` INT DEFAULT NULL AFTER `status`;
");

$installer->endSetup();
