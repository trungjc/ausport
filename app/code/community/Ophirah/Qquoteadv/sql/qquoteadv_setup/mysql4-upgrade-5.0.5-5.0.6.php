<?php
$installer = $this;
$installer->startSetup();
$quoteRequestItemTable = $installer->getTable('quoteadv_request_item');

// Add substatus
$this->run("
    ALTER TABLE `{$quoteRequestItemTable}` ADD `cost_price` decimal(12,4) default NULL;
");

$installer->endSetup();