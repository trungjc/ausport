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

class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('qquoteGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('desc');
    }


    /*
     * Adding button Create New Quote
     */
    protected function  _prepareLayout()
    {
        $this->setChild('priceupdate_deactivate_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('qquoteadv')->__('Create New Quote'),
                    'onclick' => 'setLocation(\'' . $this->getCreateQuoteUrl() . '\')',
                    'class' => 'add'
                ))

        );

        // ADDED FollowUp button
        if ($this->getRequest()->getParam('followup')){

            $data = array('label' => Mage::helper('qquoteadv')->__('Reset Follow Up'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/*') . '\')',
                'class' => ''
            );

        } else {
            $data = array('label' => Mage::helper('qquoteadv')->__('Follow Up'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/*/followup/1') . '\')',
                'class' => ''
            );
        }

        $this->setChild('follow_up',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData($data)

        );

        return parent::_prepareLayout();
    }

    /**
     * Function that gets the search button html and the priceupdate_deactivate_button block html and the follow_up block html
     *
     * @return string
     */
    public function  getSearchButtonHtml()
    {
        return parent::getSearchButtonHtml() . $this->getChildHtml('priceupdate_deactivate_button') . $this->getChildHtml('follow_up');
    }

    /**
     * Returns the Magento create order url and clears the session data
     *
     * @return mixed
     */
    public function getCreateQuoteUrl()
    {
        if (Mage::registry('current_customer')) {
            $customer = '/customer_id/' . Mage::registry('current_customer')->getId();
        } else {
            $customer = "";
        }

        // clear old session data from editing quote
        Mage::getSingleton('adminhtml/session_quote')->clear();

        return $this->getUrl('adminhtml/sales_order_create/start' . $customer);
    }

    /**
     * Setting up grid and adding data for display
     *
     * @param $column
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex == 'increment_id') $columnIndex = 'quote_id';
            $collection->setOrder($columnIndex, $column->getDir());
        }
        return $this;
    }

    /**
     * Function that prepares/gets the collection to show in the quote grid
     *
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('is_quote', '1')
            ->addFieldToFilter('customer_id', array('gt' => '0'))
            ->addFieldToFilter('status', array('gt' => Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN));

        // Adding filter for customer quote
        if (Mage::registry('current_customer')) {
            $collection = $collection->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        }

        // Adding a filter for sales rep
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $resourceLookup = "admin/sales/qquoteadv/salesrepview";
        $resourceId = $user->getData('acl')->get($resourceLookup)->getResourceId();
        if (!$user->isAllowed($resourceId)) {
            $collection = $collection->addFieldToFilter('user_id',
                array(
                    array('eq' => '0'),
                    array('eq' => $userId),
                )
            );
        }

        // Select only trial quote if in trial mode
        if (Mage::helper('qquoteadv/license')->isTrialVersion(null, true)) {
            $newCollection = clone $collection;
            $filterArray = array();
            foreach ($newCollection as $trialFilter) {
                $createHash = array($trialFilter->getCreateHash(), $trialFilter->getIncrementId());
                // Check Trial Hash
                if (Mage::helper('qquoteadv/license')->isTrialVersion($createHash)) {
                    $filterArray[] = $trialFilter->getQuoteId();
                }
            }

            // Filter Collection
            $collection = $collection->addFieldToFilter('quote_id', array('in' => $filterArray));
        }

        // ADDED filter for Follow Up
        if ($this->getRequest()->getParam('followup') == '1' && Mage::getStoreConfig('qquoteadv_advanced_settings/backend/followup') != 2) {
            $collection->addFieldToFilter('no_followup', '0')
                ->addFieldToFilter('followup', array('notnull' => 1));
            $followupIds = $this->getFollowupIds(clone $collection);

            if (is_array($followupIds)) {
                // Filter Collection
                $collection = $collection->addFieldToFilter('quote_id', array('in' => $followupIds));
            }
            // Order collection by reminder date
            if ($collection) {
                $collection->getSelect()->order('followup ASC');
            }
        }

        // ADDED filter for Follow Up
        if ($this->getRequest()->getParam('followup') == '1' && Mage::getStoreConfig('qquoteadv_advanced_settings/backend/followup') == 2) {
            $collection->addFieldToFilter(
                array(
                    'status',
                    'status',
                    'status',
                ),
                array(
                    array('eq'=>Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN_ACTION_OWNER),
                    array('eq'=>Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_OWNER),
                    array('eq'=>Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_ACTION_OWNER),
                )
            );

            // Order collection by reminder date
            if ($collection) {
                $collection->getSelect()->order('followup ASC');
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Get Quote ids for valid reminder dates
     *
     * @param     collection
     * @return    array
     */
    public function getFollowupIds($collection)
    {
        $filterArray = array();
        $currentDate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));

        // Can be used to add an option in the backend to
        // enable to only view follow up from today
        if (Mage::getStoreConfig('qquoteadv_advanced_settings/backend/followup') == 1){
            foreach ($collection as $followupFilter) {
                $followupDate = date('Ymd', Mage::getModel('core/date')->timestamp($followupFilter->getData('followup')));
                // Check Follow Up Date
                if ($currentDate <= $followupDate) {
                    $filterArray[] = $followupFilter->getQuoteId();
                }
            }

        } else {
            foreach ($collection as $followupFilter) {
                $filterArray[] = $followupFilter->getQuoteId();
            }
        }

        return $filterArray;
    }

    /**
     * Function that sets the colloums in the quote grid
     *
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('qquoteadv')->__('Quote #'),
            'align' => 'left',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('qquoteadv')->__('Created On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '160px',
        ));
        $_collection = Mage::getModel('admin/user')->getCollection();
        $adm = array();
        foreach ($_collection as $model) {
            $name = $model->getFirstname() . ' ' . $model->getLastname();
            $adm[$model->getUserId()] = $name;
        }
        $this->addColumn('user_id', array(
            'header' => Mage::helper('qquoteadv')->__('Assigned to'),
            'width' => '160px',
            'align' => 'left',
            'sortable' => true,
            'index' => 'user_id',
            'type' => 'options',
            'options' => $adm
        ));

        $this->addColumn('company', array(
            'header' => Mage::helper('customer')->__('Company'),
            'index' => 'company',
            'width' => '100',
        ));

        if(Mage::getStoreConfig('qquoteadv_advanced_settings/backend/customer_name_select') != 1){

            $this->addColumn('firstname', array(
                'header' => Mage::helper('customer')->__('First Name'),
                'index' => 'firstname'
            ));

            $this->addColumn('lastname', array(
                'header' => Mage::helper('customer')->__('Last Name'),
                'index' => 'lastname'
            ));

        } else {

            $_collection = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname');
            $cmr = array();
            foreach ($_collection as $model) {
                $name = $model->getFirstname() . ' ' . $model->getLastname();
                $cmr[$model->getId()] = $name;
            }
            natcasesort($cmr);
            $this->addColumn('customer_id', array(
                'header' => Mage::helper('customer')->__('Customer').' '.Mage::helper('customer')->__('Name'),
                'width' => '160px',
                'align' => 'left',
                'sortable' => true,
                'index' => 'customer_id',
                'type' => 'options',
                'options' => $cmr
            ));

        }

        $this->addColumn('email', array(
            'header' => Mage::helper('customer')->__('Email'),
            'width' => '160px',
            'index' => 'email'
        ));

        $this->addColumn('country_id', array(
            'header' => Mage::helper('customer')->__('Country'),
            'width' => '100px',
            'type' => 'country',
            'index' => 'country_id',
        ));

        $this->addColumn('city', array(
            'header' => Mage::helper('customer')->__('City'),
            'index' => 'city',
            'width' => '100px',
        ));

        $this->addColumn('followup', array(
            'header' => Mage::helper('qquoteadv')->__('Follow Up'),
            'index' => 'followup',
            'type' => 'date',
            'width' => '160px',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('adminhtml')->__('Status'),
            'align' => 'left',
            'width' => '160px',
            'index' => 'status',
            'type' => 'options',
            'options' => Ophirah_Qquoteadv_Model_Status::getGridOptionArray(),
            'renderer' => new Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Renderer_Status()
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('adminhtml')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('customer')->__('View'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    /**
     * Function that handles the mass actions on the quotes
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('qquote_id');
        $this->getMassactionBlock()->setFormFieldName('qquote');

        if (Mage::getSingleton('admin/session')->isAllowed('sales/qquoteadv/actions/delete')) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('adminhtml')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('adminnotification')->__('Are you sure?').'?'
            ));
        }

        $statuses = Mage::getSingleton('qquoteadv/status')->getChangeOptionArray(true);
        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('qquoteadv')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'label' => Mage::helper('adminhtml')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('set_followup', array(
            'label' => Mage::helper('qquoteadv')->__('Set Follow Up date'),
            'url' => $this->getUrl('*/*/massFollowup'),
            'additional' => array(
                'valid_from' => array(
                    'name' => 'followup',
                    'type' => 'date',
                    'label' => Mage::helper('qquoteadv')->__('Follow Up Date'),
                    'gmtoffset' => true,
                    'image' => $this->getSkinUrl('images/grid-cal.gif'),
                    'format' => '%d-%m-%Y'
                )
            )
        ));

        $this->getMassactionBlock()->addItem('export', array(
            'label' => Mage::helper('adminhtml')->__('Export'),
            'url' => $this->getUrl('*/*/export'),
        ));

        return $this;
    }

    /**
     * Returns the edit quote url for one particular quote
     *
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/qquoteadv/edit', array('id' => $row->getId()));
    }

}
