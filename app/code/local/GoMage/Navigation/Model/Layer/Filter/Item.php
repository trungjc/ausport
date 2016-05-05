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
 * @since        Class available since Release 1.0
 */

class GoMage_Navigation_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    
    public function getRemoveUrlParams()
    {    	
        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue($this->getValue()));
        $params['_nosid']       = true;
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = false;
        
        $url = Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', $params);

        $clean_url = Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>array(), '_escape'=>false));

    	if ( strpos($clean_url, "?") !== false )
    	{
        	$clean_url = substr($clean_url, 0, strpos($clean_url, '?'));
    	}
    	
    	$params = str_replace($clean_url, "", $url);
    	
    	if ($this->getFilter()->getName() == "Price")
    	{
    		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','price');
			$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
			
			if ( ($attribute->getRangeOptions() == GoMage_Navigation_Model_Adminhtml_System_Config_Source_Filter_Optionsrange::MANUALLY
						||
				  $attribute->getRangeOptions() == GoMage_Navigation_Model_Adminhtml_System_Config_Source_Filter_Optionsrange::AUTO)
					&&
				 $attribute->getFilterType() == GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT )
			{
				$params = str_replace("?", "", $params);

				$parArray = explode("&", $params);
				$newParArray = array();
				
				foreach( $parArray as $par )
				{
					$expar = explode("=", $par);
					if ( $expar[0] != 'price_from'
							&&
						 $expar[0] != 'price_to' )
					{
						$newParArray[] = $par;		
					}
				}
				
				return '?' . implode("&", $newParArray);
				
			}		
    	}
        
        return $params;
    }
    
    public function getCleanUrl($type = false)
    {        
    	$url = Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>array(), '_escape'=>false));

        if ( strpos($url, "?") !== false )
        {
            return substr($url, 0, strpos($url, '?'));
        }
    	
    	return $url;
    }
    
    public function getUrlParams($stock = false)
    {
    	$query = array(
	            $this->getFilter()->getRequestVar()=>$this->getValue(),
	            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
	        );
            
        $url = Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>$query, '_escape'=>false));
        $clean_url = Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>array(), '_escape'=>false));

    	if ( strpos($clean_url, "?") !== false )
    	{
        	$clean_url = substr($clean_url, 0, strpos($clean_url, '?'));
    	}
        
        return str_replace($clean_url, "", $url);         
    }
    
    public function getRemoveUrl($ajax = false)
    {    	
        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue($this->getValue()));
        $params['_nosid']       = true;
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = false;
        
        $params['_query']['ajax'] = null;
        
        if($ajax){
        	
        	$params['_query']['ajax'] = true;
        	
        	
        }        
        
        return Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', $params);
    }
    
    public function getUrl($ajax = false, $stock = false)
    {
    	if($this->hasData('url') && !$stock){
    		return $this->getData('url');
    	}
    	
    	$query = array(
	            $this->getFilter()->getRequestVar()=>$this->getValue(),
	            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
	        );
	    
	    $query['ajax'] = null;
	    
    	if($ajax){
        	
        	$query['ajax'] = 1;
        	
        }
        
        return Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>$query, '_escape'=>false)); 
        
    }
    
}
