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

// Add substatus
$this->run("
DROP TABLE IF EXISTS  `{$installer->getTable('quoteadv_log_admin')}`;

CREATE TABLE `{$installer->getTable('quoteadv_log_admin')}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned NOT NULL COMMENT 'Visitor ID',
  `session_id` varchar(64) DEFAULT NULL COMMENT 'Session ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Quoteadv Log Admin Table';

");

$installer->endSetup();