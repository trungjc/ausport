<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 4.0
 * @since        Class available since Release 3.2
 */

class GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Stock extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{
	
	public function prepareSelect($filter, $value, $select){

		$val = (int)$value[0];
		
        $table = "stock_status";
        $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);  
        if($val == 1)
        {
        	
            $cond = array( 
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=1 AND {$table}.is_in_stock=1",
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=0",
            );

            if ($manageStock) {
                $cond[] = "{$table}.use_config_manage_stock = 1 AND {$table}.is_in_stock=1";
            } else {
                $cond[] = "{$table}.use_config_manage_stock = 1";
            }
            $select->where("{$table}.product_id=e.entity_id");
            $select->join(  
                array($table => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
                '(' . join(') OR (', $cond) . ')',
                array("inventory_in_stock_qty"=>"qty")
            );
                
        }
        elseif($val == 2)
        {

            $cond = array(
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=1 AND {$table}.is_in_stock=0",
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=0",
            );

            if ($manageStock) {
                $cond[] = "{$table}.use_config_manage_stock = 1 AND {$table}.is_in_stock=0";
            } else {
                $cond[] = "{$table}.use_config_manage_stock = 1";
            }

            $select->where("{$table}.product_id=e.entity_id");
            $select->join(  
                array($table => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
                '(' . join(') OR (', $cond) . ')',
                array("inventory_in_stock_qty"=>"qty")
                
            ); 
        } 
        
        return $this;     
	}

     
     /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
     
     
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        
        $this->prepareSelect($filter, $value, $collection->getSelect());
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        foreach($base_select as $code=>$select){
        	
        	if('stock_status' != $code){
        	
        		$this->prepareSelect($filter, $value, $select);
        	
        	}
        }
        
        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
    	$connection = $this->_getReadAdapter();
    	
		$base_select = $filter->getLayer()->getBaseSelect();
		        
        if(isset($base_select['stock_status'])){
        	
        	$select = $base_select['stock_status'];        	
        
        }else{
        	$select = clone $filter->getLayer()->getProductCollection()->getSelect();
        	
        }

        $select->reset(Zend_Db_Select::LIMIT_COUNT);        
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        
		
        $sql = $connection->fetchAll($select);
        $productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
        
        $product_ids = array();
        foreach( $sql as $item )
        {
        	$product_ids[] = $item['entity_id'];
        }
        
        $collection = $productCollection->addAttributeToFilter('entity_id', array('in'=>$product_ids)); 
        $collection->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id');

        $stockCount = array('instock' => 0, 'outofstock' => 0);

        foreach( $collection as $product )
        {
        	if ( (int)$product->getIsInStock() > 0 )
        	{
        		$stockCount['instock']++;
        	}
        	else 
        	{
        		$stockCount['outofstock']++;
        	}
        }
        
        return $stockCount;
    }
}
