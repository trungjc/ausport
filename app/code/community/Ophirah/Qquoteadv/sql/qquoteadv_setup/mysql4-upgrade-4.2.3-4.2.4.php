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
$installer->startSetup();

// Add substatus
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_quote_address')}` ADD `vat_id` text DEFAULT null;
    ALTER TABLE `{$this->getTable('quoteadv_quote_address')}` ADD `vat_is_valid` smallint(6) DEFAULT null;
    ALTER TABLE `{$this->getTable('quoteadv_quote_address')}` ADD `vat_request_id` text DEFAULT null;
    ALTER TABLE `{$this->getTable('quoteadv_quote_address')}` ADD `vat_request_date` text DEFAULT null;
    ALTER TABLE `{$this->getTable('quoteadv_quote_address')}` ADD `vat_request_success` smallint(6) DEFAULT null;
");

$installer->endSetup();