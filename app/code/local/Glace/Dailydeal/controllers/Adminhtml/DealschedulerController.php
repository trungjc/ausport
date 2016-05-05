<?php

class Glace_Dailydeal_Adminhtml_DealschedulerController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('promo');
        $this->_title(Mage::helper('dailydeal')->__("Deal Generator"));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('dailydeal/dealscheduler')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            if (Mage::getSingleton('dailydeal/dealscheduler')->getFlag() == 'dailyschedule') {
                $model->setData('start_date_time', $this->getRequest()->getParam('start'));
            }

            Mage::register('dealscheduler_data', $model);

            $products = Glace_Dailydeal_Model_Dealschedulerproduct::getInstance()->getProductOptionArray($id);
            Mage::register('products', $products);

            $this->loadLayout();
            $this->_setActiveMenu('dealscheduler/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Deal Manager'), Mage::helper('adminhtml')->__('Deal Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Deal News'), Mage::helper('adminhtml')->__('Deal News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock(new Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit()))
                    ->_addLeft($this->getLayout()->createBlock(new Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Tabs()));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Deal does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {

        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        
        $data['radioproduct'] = array();
        $deal = array();
        if(isset($data['links'])){
            $data['radioproduct'] = $this->decodeProductId($data['links']['upsell']);
            $deal = $this->decodeDeal($data['links']['upsell']);
        }
        
        $data = array_merge($data, $deal);

        try {
            // Many form => change name input
            $data['deal_time'] = $data['deal_scheduler_time'];
            $data['deal_price'] = $data['deal_scheduler_price'];
            $data['deal_qty'] = $data['deal_scheduler_qty'];

            $model = Glace_Dailydeal_Model_Dealscheduler::getModel();

            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
            
            if (!empty($data['links'])) {
                // User has choice product
                $product_scheduler = Glace_Dailydeal_Model_Dealschedulerproduct::getModel();
                $product_scheduler->deleteAllByDealSchedulerId($model->getId());

                foreach ($data['radioproduct'] as $product_id) {
                    $product_scheduler->setData(array());
                    $product_scheduler->setData('product_id', $product_id);
                    $product_scheduler->setData('deal_scheduler_id', $model->getId());
                    
                    $key_time = 'deal_time_' . $product_id;
                    $key_price = 'deal_price_' . $product_id;
                    $key_qty = 'deal_qty_' . $product_id;
                    $key_position = 'deal_position_' . $product_id;
                    
                    if (isset($data[$key_time]))
                        $product_scheduler->setData('deal_time', $data[$key_time]);
                    if (isset($data[$key_price]) != '')
                        $product_scheduler->setData('deal_price', $data[$key_price]);
                    if (isset($data[$key_qty]) != '')
                        $product_scheduler->setData('deal_qty', $data[$key_qty]);
                    if (isset($data[$key_position]) != '')
                        $product_scheduler->setData('deal_position', $data[$key_position]);
                    $product_scheduler->save();
                }
            }

            // Add notifications
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dailydeal')->__('Deal Generator was saved successfully!'));
            Mage::getSingleton('adminhtml/session')->setFormData(true);
            
            if (!empty($data['rule_auto_generate_deal'])) {
                $count = Glace_Dailydeal_Model_Business::generalDeal($model->getId());

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dailydeal')->__('Generate %s deals successful!', $count));
                $this->_redirect('*/*/');
                return;
            }

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (Exception $ex) {

            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Unable to find Deal Generator to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $deal = Mage::getModel('dailydeal/dealscheduler')->load($id);
                $deal->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Deal Generator was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $testIds = $this->getRequest()->getParam('dailydeal');
        if (!is_array($testIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($testIds as $testId) {
                    $test = Mage::getModel('dailydeal/dealscheduler')->load($testId);
                    $test->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($testIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $testIds = $this->getRequest()->getParam('dailydeal');
        if (!is_array($testIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Please select item(s)'));
        } else {
            try {
                foreach ($testIds as $testId) {
                    $test = Mage::getSingleton('dailydeal/dealscheduler')
                            ->load($testId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        Mage::helper('dailydeal')->__('Total of %d record(s) were successfully updated', count($testIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function ajaxIsValidDealPriceAction()
    {
        $data = $this->getRequest()->getPost();

        $model = Glace_Dailydeal_Model_Dealscheduler::getModel();
        $flag = $model->isValidDealPrice($data['deal_price']);

        if ($flag) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function ajaxIsValidDealQtyAction()
    {
        $data = $this->getRequest()->getPost();

        $model = Glace_Dailydeal_Model_Dealscheduler::getModel();
        $flag = $model->isValidDealQty($data['deal_qty']);

        if ($flag) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function productAction()
    {
        $id = $this->getRequest()->getParam('id');
        $products = Glace_Dailydeal_Model_Dealschedulerproduct::getInstance()->getProductOptionArray($id);
        Mage::register('products', $products);

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Ajax list product
     */
    public function gridProductAction()
    {
        $id = $this->getRequest()->getParam('id');
        $products = Glace_Dailydeal_Model_Dealschedulerproduct::getInstance()->getProductOptionArray($id);
        Mage::register('products', $products);

        $response = $this->getLayout()->createBlock(new Glace_Dailydeal_Block_adminhtml_Dealscheduler_Edit_Product_Grid())->getHtml();
        $this->getResponse()->setBody($response);
    }

    protected function decodeDeal($str_encode)
    {
        $result = array();

        $str_products = explode("&", $str_encode);

        foreach ($str_products as $value) {

            $product = explode("=", $value);
            $product_id = $product[0];

            // Remove %3D
            $deal_encode = rtrim($product[1], '%3D');
            $deal_values = explode("&", base64_decode($deal_encode));

            foreach ($deal_values as $deal_key => $deal_string) {
                $deal_value = array();
                $deal_value = Glace_Dailydeal_Helper_Toolasiaconnect::exploseEqualToArray($deal_string);

                if (!empty($deal_value['deal_time'])) {
                    $result['deal_time_' . $product_id] = $deal_value['deal_time'];
                } elseif (!empty($deal_value['deal_price'])) {
                    $result['deal_price_' . $product_id] = $deal_value['deal_price'];
                } elseif (!empty($deal_value['deal_qty'])) {
                    $result['deal_qty_' . $product_id] = $deal_value['deal_qty'];
                } elseif (!empty($deal_value['deal_position'])) {
                    $result['deal_position_' . $product_id] = $deal_value['deal_position'];
                }
            }
        }

        return $result;
    }

    protected function decodeProductId($str_encode)
    {
        $result = array();

        if (!empty($str_encode)) {

            $str_products = explode("&", $str_encode);

            foreach ($str_products as $key => $value) {
                $product = explode("=", $value);
                $product_id = $product[0];
                $result[$product_id] = $product_id;
            }
        }
        ksort($result);

        return $result;
    }

}