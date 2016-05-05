<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_selection');
        $this->setDefaultSort('id');
        $this->setTemplate('glace_dailydeal/dealitems/product/grid.phtml');
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
        $this->setRowClickCallback("Zizio_Groupsale_OnProductSelectGridCheckboxCheck");
    }

    protected function _beforeToHtml()
    {
        $this->setId($this->getId() . '_' . $this->getIndex());
        $this->getChild('reset_filter_button')->setData('onclick', $this->getJsObjectName() . '.resetFilter()');
        $this->getChild('search_button')->setData('onclick', $this->getJsObjectName() . '.doFilter()');
        return parent::_beforeToHtml();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites', 'catalog/product_website', 'website_id', 'product_id=entity_id', null, 'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('visibility', array('gt' => '1'))
                ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->addFieldToFilter('is_in_stock', 1);

        $store = $this->_getStore();
        if ($store->getId()) {
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        // $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    public function getHtml()
    {
        try {

            $html = parent::getHtml();
            $collection = $this->getCollection();
            $extra_data = array();
            $items = $collection->getItems();
            $types = Mage::getModel('catalog/product_type')->getOptionArray();
            foreach ($items as $item) {
                $type_id = $item->getTypeId();
                $extra_data[$item->getEntityId()] = array(
                    "id" => $item->getId(),
                    "name" => $item->getName(),
                    "sku" => $item->getSku(),
                    "qty" => round($item->getQty(), 0),
                    "url_key" => $item->getUrlKey(),
                    "desc" => $item->getDescription(),
                    "meta_desc" => $item->getMetaDescription(),
                    "small_img" => Glace_Dailydeal_Helper_Data::GetProductImage($item, true),
                    "price" => round($item->getPrice(), 2),
                    "category_ids" => implode(',', $item->getCategoryIds()),
                    //"url"		=>	Mage::getModel('catalog/product')->load($item->getId())->getUrlPath(),
                    "curr_sym" => Glace_Dailydeal_Helper_Data::GetBaseCurrencySymbol(),
                    "curr_code" => Glace_Dailydeal_Helper_Data::GetBaseCurrencyCode(),
                    "type_id" => $type_id,
                    "type" => isset($types[$type_id]) ? $types[$type_id] : $type_id
                );
            }

            $json = Glace_Dailydeal_Helper_Data::json_encode($extra_data);
            // don't add var before the product_extra_data variable, this function is
            // also called in Ajax, so we must overwrite the global variable.
            return sprintf("<script type='text/javascript'>product_extra_data = %s</script>", $json) . $html;
        } catch (Exception $ex) {
            Glace_Dailydeal_Helper_Data::LogError($ex);
        }
    }

    protected function _prepareColumns()
    {
        try {
            $this->addColumn('prd_entity_id', array(
                'header' => Mage::helper('adminhtml')->__('ID'),
                'sortable' => true,
                'index' => 'entity_id'
            ));

            $this->addColumn('prd_name', array(
                'header' => Mage::helper('catalog')->__('Name'),
                'index' => 'name',
                'width' => '30px', // filter product error width => fix width
                'column_css_class' => 'name'
            ));

            $this->addColumn('prd_type', array(
                'header' => Mage::helper('catalog')->__('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => Mage::getModel('catalog/product_type')->getOptionArray(),
            ));

            $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                    ->load()
                    ->toOptionHash();

            $this->addColumn('set_name', array(
                'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ));

            $this->addColumn('prd_sku', array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'index' => 'sku',
                'column_css_class' => 'sku'
            ));

            $this->addColumn('prd_price', array(
                'header' => Mage::helper('sales')->__('Price'),
                'align' => 'center',
                'type' => 'price',
                'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
                'rate' => $this->_getStore()->getBaseCurrency()->getRate($this->_getStore()->getBaseCurrency()->getCode()),
                'index' => 'price'
            ));

            $this->addColumn('prd_qty', array(
                'header' => Mage::helper('catalog')->__('Qty'),
                'type' => 'number',
                'index' => 'qty',
            ));

            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('websites', array(
                    'header' => Mage::helper('catalog')->__('Websites'),
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
            }
        } catch (Exception $ex) {
            Glace_Dailydeal_Helper_Data::LogError($ex);
        }
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $ret = $this->getUrl('dailydeal/adminhtml_dealitems/gridProduct', array(
            'index' => $this->getIndex(),
            '_current' => true,
                ));
        return $ret;
    }

    protected function _getStore()
    {
        return Mage::app()->getStore($this->getRequest()->getParam('store'));
    }

}