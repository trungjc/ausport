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

$sql = "SELECT request_id, product_id FROM {$this->getTable('quoteadv_request_item')}  where original_price = 0";
$result = $installer->getConnection()->fetchAll($sql);
$cacheSql = array();
foreach ($result as $item) {
    $requestId = $item['request_id'];
    $productId = $item['product_id'];
    $price = 0;

    $sqlSearch = " 
				SELECT distinct final_price
				FROM `{$this->getTable('catalog_product_entity')}`  AS `e` 
				INNER JOIN  `{$this->getTable('catalog_product_index_price')}` AS `indprice` 
				ON indprice.entity_id = e.entity_id 
				where indprice.entity_id=$productId";

    $cacheSql[$productId] = $sqlSearch;
    $searchResult = $installer->getConnection()->fetchAll($sqlSearch);

    foreach ($searchResult as $res) {
        $price = $res['final_price'];
        if ($price > 0) {
            $update = "UPDATE {$this->getTable('quoteadv_request_item')} SET `original_price`='" . $price . "' WHERE (`request_id`='" . $requestId . "')";
            $installer->run($update);
        }
    }
}
$installer->endSetup();