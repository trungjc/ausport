<?php

class Glace_Dailydeal_Adminhtml_DealitemsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
                ->renderLayout();
    }
    
    public function alldealsAction(){
        Mage::app()->getRequest()->setParam('filter', '');
        $this->_forward('index');
    }

    public function currentdealsAction(){
        $filter = 'active=1';
        $encode_filter = base64_encode($filter);
        Mage::app()->getRequest()->setParam('filter', $encode_filter);
        $this->_forward('index');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('dailydeal/dailydeal')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            if (Mage::getSingleton('dailydeal/dailydeal')->getFlag() == 'dailyschedule') {
                $model->setData('start_date_time', $this->getRequest()->getParam('start'));
            }

            Mage::register('dealitems_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('dealitems/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Deal Manager'), Mage::helper('adminhtml')->__('Deal Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Deal News'), Mage::helper('adminhtml')->__('Deal News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_edit'))
                    ->_addLeft($this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Deal does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        //var_dump(Mage::getSingleton('adminhtml/session')->getFlag() == 'dailyschedule');die();
        Mage::getSingleton('adminhtml/session')->setFlag('dealitems');

        $this->_forward('edit');
    }

    public function newdailyAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();

        try {
            if (($this->getRequest() == null) || ($this->getRequest()->getPost() == null)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Unable to find deal to save'));
                $this->_redirect('*/*/');
                return;
            }

            // Many form => change name input
            $data['status'] = $data['deal']['status'];
            unset($data['deal']);

            $model = Glace_Dailydeal_Model_Dailydeal::getModel()
                    ->setData($data)
                    ->setId($id)
                    ->save();

            // Add notifications
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dailydeal')->__('Deal was saved successfully!'));
            Mage::getSingleton('adminhtml/session')->setFormData(true);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }

            if (Mage::getSingleton('adminhtml/session')->getFlag() == 'dailyschedule')
                $this->_redirect('*/adminhtml_dailyschedule/days/');
            else
                $this->_redirect('*/adminhtml_dealitems/index/');
            return;
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('*/adminhtml_dailyschedule/days/');
    }

    public function exportCsvAction()
    {
        $fileName = 'dealitems.csv';
        $content = $this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'dealitems.xml';
        $content = $this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $deal = Mage::getModel('dailydeal/dailydeal')->load($id);
                $deal->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
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
                    $test = Mage::getModel('dailydeal/dailydeal')->load($testId);
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
                    $test = Mage::getSingleton('dailydeal/dailydeal')
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

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function gridProductAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('dailydeal/dailydeal')->load($id);

        if ($model->getId() || $id == 0) {
            Mage::register('dealitems_data', $model);
        }

        // for ajax call
        $response = $this->getLayout()->createBlock('dailydeal/adminhtml_dealitems_edit_product_grid')->getHtml();
        $this->getResponse()->setBody($response);
    }

    /**
     * for ajax call
     */
    public function gridOrderAction()
    {
        $response = $this->getLayout()->createBlock(new Glace_Dailydeal_Block_Adminhtml_Sales_Order_Grid())->getHtml();
        $this->getResponse()->setBody($response);
    }

}