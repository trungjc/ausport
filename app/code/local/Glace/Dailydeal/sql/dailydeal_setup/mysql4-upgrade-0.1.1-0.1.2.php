<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DELETE FROM {$this->getTable('core_url_rewrite')} WHERE request_path LIKE 'daily-deals';
DELETE FROM {$this->getTable('core_url_rewrite')} WHERE request_path LIKE 'daily-deals/comming';
DELETE FROM {$this->getTable('core_url_rewrite')} WHERE request_path LIKE 'daily-deals/past';

INSERT INTO {$this->getTable('core_url_rewrite')} (`id_path`, `request_path`, `target_path`, `is_system`) VALUES ('dailydeal/index', 'daily-deals','dailydeal/index/index',1);
INSERT INTO {$this->getTable('core_url_rewrite')} (`id_path`, `request_path`, `target_path`, `is_system`) VALUES ('dailydeal/past', 'daily-deals/past','dailydeal/past/index',1);
INSERT INTO {$this->getTable('core_url_rewrite')} (`id_path`, `request_path`, `target_path`, `is_system`) VALUES ('dailydeal/comming', 'daily-deals/comming','dailydeal/comming/index',1);

    ");

$installer->endSetup(); 