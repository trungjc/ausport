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

$this->startSetup();

$this->run("
  DROP TABLE IF EXISTS `{$this->getTable('quoteadv_rotation')}`;

  CREATE TABLE `{$this->getTable('quoteadv_rotation')}` (
    `rotation_id` int(10) unsigned NOT NULL auto_increment,
    `user_id` int(10) unsigned NOT NULL,
    `role_id` int(10) unsigned NOT NULL,
    `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
    `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (`rotation_id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes';

  ALTER TABLE `{$this->getTable('quoteadv_rotation')}`
    ADD CONSTRAINT `FK_ quoteadv_rotation_user_id` FOREIGN KEY (`user_id`) REFERENCES `{$this->getTable('admin/user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `FK_ quoteadv_rotation_role_id` FOREIGN KEY (`role_id`) REFERENCES `{$this->getTable('admin/role')}` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();