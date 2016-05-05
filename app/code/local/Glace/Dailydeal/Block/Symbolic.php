<?php
class Glace_Dailydeal_Block_Symbolic extends Mage_Core_Block_Template
{
	 public function __construct()
    {
        $customerSession = Mage::getSingleton('customer/session');

        parent::__construct();

        $data =  Mage::getSingleton('review/session')->getFormData(true);
        $data = new Varien_Object($data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag($customerSession->isLoggedIn() || Mage::helper('review')->getIsGuestAllowToWrite());
        if (!$this->getAllowWriteReviewFlag) {
            $this->setLoginLink(
                Mage::getUrl('customer/account/login/', array(
                    Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(
                        Mage::getUrl('*/*/*', array('_current' => true)) .
                        '#review-form')
                    )
                )
            );
        }

        $this->setTemplate('review/form.phtml')
            ->assign('data', $data)
            ->assign('messages', Mage::getSingleton('review/session')->getMessages(true));
    }
    public function getProductInfo()
    {
        $product = Mage::getModel('catalog/product');
        return $product->load($this->getRequest()->getParam('id'));
    }
    protected $_reviewsCollection;
    public function getReviewsCollection($productid)
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $productid)
                ->setDateOrder();
        }
        return $this->_reviewsCollection;
    }
    public function getReviewUrl($id)
    {
        return Mage::getUrl('*/*/view', array('id' => $id));
    }
    public function __getPercentRate($review){
    	$collection = Mage::getModel('rating/rating_option_vote')->getCollection()
    		->addFieldToFilter('review_id',$review->getReviewId());
    	$rate = 0;
    	if($collection){
    		foreach($collection as $rating){
    			$rate += $rating->getPercent();
    		}
    	}
    	
    	if(sizeof($collection->getData())!=0){
    		return $rate/sizeof($collection->getData());
    	}else{
    		return 0;
    	}
    }
	public function _prepareLayout()
    { 
		$this->setTemplate('glace_dailydeal/symbolicdeal.phtml');
	//	return parent::_prepareLayout();
    }
    /**
     * Retrieve url for direct adding product to cart
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }

        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }

        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => false));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);

        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }
    //getcollection 1 mang cac deal kich hoat, sap xep theo tu tu kich hoat trc tu tren xuong
    //de dang chon dc deal dc uu tien
    public function getDeals()
    {
        $store_id = Mage::app()->getStore()->getId();
        
        $tblCatalogStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

        $collection = Glace_Dailydeal_Model_Dailydeal::getModel()->getCollection()
                ->addFieldToFilter('status', Glace_Dailydeal_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('expire', Glace_Dailydeal_Model_Status::STATUS_EXPIRE_FALSE)
                ->addFieldToFilter('store_view', array(array('like' => '%' . Mage::app()->getStore()->getId() . '%'), array('like' => '0')))
                ->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime))
                ->addProductStatusFilter($store_id)
                ->getConfigSortBy();
        
        $collection->getSelect()->joinLeft(
                array('stock' => $tblCatalogStockItem), 'stock.product_id = main_table.product_id', array('stock.qty', 'stock.is_in_stock')
        );

        $collection->getSelect()->where("stock.is_in_stock = " . Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

        return $collection;
    }

}