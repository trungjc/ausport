<?php
class Glace_Dailydeal_Block_Showtabs_Tab extends Mage_Core_Block_Template
{
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	
	public function getActivedealUrl(){
		return "";
	}
	
	public function getCommingdealUrl(){
		return "";	
	}
	
	public function getPastdealUrl(){
		return "";
	}
}