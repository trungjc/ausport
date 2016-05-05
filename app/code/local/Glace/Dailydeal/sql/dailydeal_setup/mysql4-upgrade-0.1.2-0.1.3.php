<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'activedeal', array(
	'label' => 'Active Deal',
	'type' => 'int',
	'input' => 'hidden',	
	'required' => false,
	'position' => 10,
));

/*$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('glace_dailydeal_active')};
CREATE TABLE {$this->getTable('glace_dailydeal_active')} (
  `id`	int(11) unsigned DEFAULT NOT NULL, 
  `dailydeal_price` float(11) DEFAULT '0', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();*/ 