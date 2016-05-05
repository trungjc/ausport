<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('dailydeal/dailydeal')}
    ADD `order_id` text default '',
    ADD `store_view` text default '',
    ADD `limit_customer` int(11),
    ADD `disable_product_after_finish` int(11),
    ADD `expire`  int(11) DEFAULT '1',
    ADD `active`  int(11);
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('dailydeal/dealscheduler')};
CREATE TABLE {$this->getTable('dailydeal/dealscheduler')} (
    `deal_scheduler_id` int(11) unsigned NOT NULL auto_increment,
    `title` varchar(1024),
    `deal_time` int (11),
    `deal_price` varchar(1024),
    `deal_qty` varchar(1024),
    `number_deal` int(11),
    `number_day` int(11),
    `generate_type` int(11),
    `start_date_time` datetime DEFAULT NULL,
    `end_date_time` datetime DEFAULT NULL,
    `status` smallint(6) NOT NULL,
  PRIMARY KEY (`deal_scheduler_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
ALTER TABLE {$this->getTable('dailydeal/dailydeal')}     ADD `deal_scheduler_id`  int(11);
ALTER TABLE {$this->getTable('dailydeal/dailydeal')}     ADD `thread`  int(11);
ALTER TABLE {$this->getTable('dailydeal/dailydeal')}     ADD `product_price` float NULL AFTER `product_sku`;
");

    
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('dailydeal/dealschedulerproduct')};
CREATE TABLE {$this->getTable('dailydeal/dealschedulerproduct')} (
    `dealschedulerproduct_id` bigint(11) unsigned auto_increment,
    `deal_scheduler_id` int(11) unsigned,
    `product_id` int(11) unsigned,
    `deal_time` int (11),
    `deal_price` float(11),
    `deal_qty` int(11),
    `deal_position` int(11),
  PRIMARY KEY (`dealschedulerproduct_id`),
  FOREIGN KEY (`deal_scheduler_id`) REFERENCES {$this->getTable('dailydeal/dealscheduler')}     (`deal_scheduler_id`)   ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')}                    (`entity_id`)           ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

// Update data, version form 1.3.4 to 2.1.3
$installer->run("
    UPDATE {$this->getTable('glace_dailydeal')} SET
        store_view = '0',
        limit_customer = '0',
        expire = '0',
        active = '0',
        disable_product_after_finish = '0',
        discount_type = '4',
        discount = dailydeal_price;
    ");
$installer->run("
    UPDATE {$this->getTable('glace_dailydeal')} SET
    featured = '2'
    WHERE featured = '0';
");
$installer->endSetup();
