<?php
/**
 * Webline Magento
 * 
 * Setup for add Custom attribute "Sort order"
 *
 * @category   Wli
 * @package    Wli_Productsortbywebline
 * @author     Webline Magento Team
 */  
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup'); 
$installer->startSetup();

$data=array(
    'type'=>'int',
    'input'=>'text', 
    'sort_order'=> 1, 
    'label'=>'Sort order',
    'global'=>Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'is_required'=>'0',
    'comparable'=>'0',
    'searchable'=>'0',
    'is_configurable'=>'1',
    'user_defined'=>'1',
    'default' => '',
    'visible_on_front' => 0, 
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'required'=> 0,
    'unique'=> true,
	'frontend_class'=>'validate-digits',
    'apply_to' => '',
    'is_configurable' => false
);

$installer->addAttribute('catalog_product','wli_sort_order',$data);
$installer->addAttributeToSet(
    'catalog_product', 'Default', 'General', 'wli_sort_order'
);
$installer->endSetup();