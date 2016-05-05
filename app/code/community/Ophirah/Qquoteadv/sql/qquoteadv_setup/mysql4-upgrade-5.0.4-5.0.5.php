<?php
$installer = $this;
$installer->startSetup();
$quoteAddressTable = $installer->getTable('quoteadv_quote_address');

// Add substatus
$this->run("
    ALTER TABLE `{$quoteAddressTable}` ADD `subtotal_incl_tax` decimal(12,4) default '0.0000';
    ALTER TABLE `{$quoteAddressTable}` ADD `base_subtotal_incl_tax` decimal(12,4) default '0.0000';
");

$installer->endSetup();