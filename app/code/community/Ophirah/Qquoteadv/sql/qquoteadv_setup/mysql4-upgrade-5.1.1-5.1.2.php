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

// Tables
Const PRODUCT_DOWNLOADABLE = 'qquoteadv/qqadvproductdownloadable';
Const PRODUCT = 'qquoteadv/qqadvproduct';
Const DOWNLOADABLE = 'downloadable/link';

$installer = $this;
$installer->startSetup();

/**
 * Create 'Product_Downloadable' Table
 */
if(!$installer->getConnection()->isTableExists($this->getTable(PRODUCT_DOWNLOADABLE))){
    $table = new Varien_Db_Ddl_Table();
    $table->setName($this->getTable(PRODUCT_DOWNLOADABLE));
    $table->addIndex('id', 'id');
    $table->setOption('type', 'InnoDB');
    $table->setOption('charset', 'utf8');
    $table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'auto_increment'    => true,
            'unsigned'          => true,
            'nullable'          => false,
            'primary'           => true,
            'comment'           => 'Product Downloadable Id')
    );
    $table->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'primary'   => false,
            'nullable'  => false,
            'comment'   => 'Quoteadv Product Id')
    );
    $table->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'primary'   => false,
            'nullable'  => false,
            'comment'   => 'Downloadable Id')
    );
    $table->setComment('Product Downloadable many-to-many relationship Table');
    $installer->getConnection()->createTable($table);
};

/**
 * Add foreign key Constraint - Quoteadv_Product_Downloadable -> Quoteadv_Product
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        PRODUCT_DOWNLOADABLE,  'product_id',
        PRODUCT,  'id'
    ),
    $installer->getTable(PRODUCT_DOWNLOADABLE), 'product_id',
    $installer->getTable(PRODUCT), 'id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

/**
 * Add foreign key Constraint - Quoteadv_Product_Downloadable -> Downloadable_Link
 */
$tableDownload = $installer->getTable(DOWNLOADABLE);
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        PRODUCT_DOWNLOADABLE,  'link_id',
        DOWNLOADABLE,  'link_id'
    ),
    $installer->getTable(PRODUCT_DOWNLOADABLE), 'link_id',
    $installer->getTable(DOWNLOADABLE), 'link_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();

