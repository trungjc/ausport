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
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('quoteadv_customer'),
    "currency",
    "varchar(4)");

$installer->getConnection()->addColumn($installer->getTable('quoteadv_customer'),
    "no_expiry",
    "tinyint(1) default '0'");

$installer->getConnection()->addColumn($installer->getTable('quoteadv_customer'),
    "base_to_quote_rate",
    "DECIMAL(12,4) NOT NULL default '0.0000'");

$installer->getConnection()->addColumn($installer->getTable('quoteadv_customer'),
    'expiry',
    'date');

$installer->getConnection()->addColumn($installer->getTable('quoteadv_request_item'),
    "owner_cur_price",
    "DECIMAL(12,4) NOT NULL default '0.0000'");

$installer->getConnection()->addColumn($installer->getTable('quoteadv_request_item'),
    "original_cur_price",
    "DECIMAL(12,4) NOT NULL default '0.0000'");


//ALTER TABLE quoteadv_request_item DROP FOREIGN KEY `FK_quoteadv_product_id`

$installer->endSetup();