<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('glace_dailydeal')};
CREATE TABLE {$this->getTable('glace_dailydeal')} (
  `dailydeal_id` int(11) unsigned NOT NULL auto_increment,
  `product_id`	int(11) DEFAULT NULL,
  `cur_product` varchar(1024) DEFAULT NULL,
  `product_sku` varchar(255) DEFAULT NULL,
  `discount` float(11) DEFAULT '0',
  `discount_type` int(11) DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `dailydeal_price` float(11) DEFAULT '0',
  `deal_qty` int(11) NOT NULL DEFAULT '0',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `description` text,
  `website_ids` text,
  `website_id` int(11) DEFAULT NULL,
  `store_ids` text,
  `customer_group_ids` text,
  `promo` text,
  `featured` smallint(6) DEFAULT '0',
  `disableproduct` smallint(6) DEFAULT '0', 
  `requiredlogin` smallint(6) DEFAULT '0',
  `impression` int(32) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dailydeal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 