<?php

class Glace_Dailydeal_Block_Adminhtml_Dealitems_Edit_Conf_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('dailydeal/deal')
                ->setTemplate('glace_dailydeal/dealitems/javascript_form_deal.phtml');
        $javacript = $block->renderView();
        
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('dailydeal_form', array(
            'legend' => Mage::helper('dailydeal')->__('Deal Information'),
                ));

        $fieldset->addField('product_id', 'hidden', array(
            'name' => 'product_id',
            'after_element_html' =>
            "<script type='text/javascript'>
                    var url_product_id = '{$this->getUrl('adminhtml/catalog_product/edit/id/{{product_id}}')}';
                </script>",
        ));

        $fieldset->addField('product_type', 'hidden', array(
            'name' => 'product_type',
        ));

        $fieldset->addField('sold_qty', 'hidden', array(
            'name' => 'sold_qty',
        ));
        
        $fieldset->addField('cur_product', 'text', array(
            'name' => 'cur_product',
            'label' => Mage::helper('dailydeal')->__('Product Name'),
            'readonly' => true,
            'class' => 'textbox-readonly required-entry',
            'after_element_html' => $javacript,
        ));

        $fieldset->addField('product_sku', 'text', array(
            'name' => 'product_sku',
            'label' => Mage::helper('dailydeal')->__('Product Sku'),
            'readonly' => true,
            'class' => 'textbox-readonly',
        ));

        $fieldset->addField('product_price', 'text', array(
            'name' => 'product_price',
            'label' => Mage::helper('dailydeal')->__('Product Price') . ' ' . Glace_Dailydeal_Helper_Data::GetCurrencyCodeHtml(),
            'readonly' => true,
            'class' => 'textbox-readonly',
        ));

        $fieldset->addField('product_qty', 'text', array(
            'name' => 'product_qty',
            'label' => Mage::helper('dailydeal')->__('Product Qty'),
            'readonly' => true,
            'class' => 'textbox-readonly',
            'after_element_html' => '
                    <br /><br />
                    </td>
                </tr>
                <tr class="system-fieldset-sub-head">
                        <td colspan="2">
                                <h4 style="border-bottom: 1px solid #CCCCCC; width : 100%">'. Mage::helper('dailydeal')->__('Deal Setting') . '</h4>',
        ));

        $fieldset->addField('percent_price', 'text', array(
            'name' => 'percent_price',
            'label' => Mage::helper('dailydeal')->__('% Discount'),
            'title' => Mage::helper('dailydeal')->__('% Discount'),
            'class' => 'validate-number',
        ));
        
        $fieldset->addField('dailydeal_price', 'text', array(
            'name' => 'dailydeal_price',
            'label' => Mage::helper('dailydeal')->__('Deal Price'),
            'title' => Mage::helper('dailydeal')->__('Deal Price'),
            'class' => 'required-entry validate-number validate-zero-or-greater custom-deal-price',
            'required' => true,
        ));

        $fieldset->addField('deal_qty', 'text', array(
            'name' => 'deal_qty',
            'label' => Mage::helper('dailydeal')->__('Deal Qty'),
            'title' => Mage::helper('dailydeal')->__('Deal Qty'),
            'class' => 'validate-number validate-zero-or-greater custom-deal-qty',
            'note' => Mage::helper('dailydeal')->__('Leave blank for no limit'),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_view', 'multiselect', array(
                'name' => 'store_view[]',
                'label' => Mage::helper('dailydeal')->__('Store View'),
                'title' => Mage::helper('dailydeal')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        } else {
            $fieldset->addField('store_view', 'hidden', array(
                'name' => 'store_view[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }

        $fieldset->addField('limit_customer', 'text', array(
            'name' => 'limit_customer',
            'label' => Mage::helper('dailydeal')->__('Limit deals per customer order'),
            'title' => Mage::helper('dailydeal')->__('Limit deals per customer order'),
            'class' => 'validate-number validate-zero-or-greater',
            'note' => Mage::helper('dailydeal')->__('Leave empty or 0 for unlimited deal qty per order'),
        ));

        $fieldset->addField('disable_product_after_finish', 'select', array(
            'name' => 'disable_product_after_finish',
            'label' => Mage::helper('dailydeal')->__('Disable product after deal ends'),
            'title' => Mage::helper('dailydeal')->__('Disable product after deal ends'),
            'values' => Glace_Dailydeal_Model_Status::getProductOptionArray(),
            'note' => Mage::helper('dailydeal')->__('Default:No'),
        ));

        $fieldset->addField('start_date_time', 'date', array(
            'name' => 'start_date_time',
            'title' => Mage::helper('dailydeal')->__('Active From'),
            'label' => Mage::helper('dailydeal')->__('Active From'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => true,
            'class' => 'required-entry',
            'required' => true,
            'format' => "yyyy-MM-dd h:mm:ss a",
            'readonly' => true,
            'disabled' => false,
            'style' => 'width: 200px',
        ));

        $fieldset->addField('end_date_time', 'date', array(
            'name' => 'end_date_time',
            'title' => Mage::helper('dailydeal')->__('Active To'),
            'label' => Mage::helper('dailydeal')->__('Active To'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => true,
            'class' => 'required-entry',
            'required' => true,
            'format' => "yyyy-MM-dd h:mm:ss a",
            'readonly' => true,
            'disabled' => false,
            'style' => 'width: 200px',
        ));

        $fieldset->addField('featured', 'select', array(
            'name' => 'featured',
            'label' => Mage::helper('dailydeal')->__('Featured Deal'),
            'title' => Mage::helper('dailydeal')->__('Featured Deal'),
            'values' => Glace_Dailydeal_Model_Status::getFeaturedOptionArray(),
            'note' => Mage::helper('dailydeal')->__('Featured Deals appear first/highest in deal blocks'),
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'deal[status]',
            'label' => Mage::helper('dailydeal')->__('Status'),
            'title' => Mage::helper('dailydeal')->__('Status'),
            'values' => Glace_Dailydeal_Model_Status::getOptionArray(),
            'note' => Mage::helper('dailydeal')->__('Enable and Save Deal to activate'),
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('catalog')->__('Deal Description'),
            'title' => Mage::helper('catalog')->__('Deal Description'),
        ));

        if (Mage::getSingleton('adminhtml/session')->getDealitemsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getDealitemsData());
            Mage::getSingleton('adminhtml/session')->setDealitemsData(null);
        } elseif (Mage::registry('dealitems_data')) {

            $id = $this->getRequest()->getParam("id");
            if (empty($id)) {
                $form->setValues($this->setAddFormDefaultValue());
            } else {
                $form->setValues($this->setEditFormDefaultValue());
            }
            
            if ($this->getRequest()->getParam('start')) {
                $form->setValues($this->setValuesFromSchedule());
            }
        }
        return parent::_prepareForm();
    }

    /**
     * Set default data for action add
     * @return array
     */
    protected function setAddFormDefaultValue()
    {
        $data = Mage::registry('dealitems_data')->getData();

        $data['limit_customer'] = empty($data['limit_customer']) ? 0 : $data['limit_customer'];
        $data['status'] = empty($data['status']) ? Glace_Dailydeal_Model_Status::STATUS_ENABLED : $data['status'];
        $data['featured'] = empty($data['featured']) ? Glace_Dailydeal_Model_Status::STATUS_FEATURED_DISABLED : $data['featured'];
        $data['disable_product_after_finish'] = empty($data['disable_product_after_finish']) ? Glace_Dailydeal_Model_Status::STATUS_PRODUCT_DISABLED : $data['disable_product_after_finish'];
        $data['count_active_customer'] = '<input type="text"></input>';

        // Javascript datepicker update fail format : true is( 2013-04-01 4:00:00 PM ) -> ( 2013-04-01 14:00:00 )
        if (!empty($data['start_date_time'])) {
            $obj_datetime = new DateTime($data['start_date_time']);
            $data['start_date_time'] = $obj_datetime->format('Y-m-d H:i:s');
        }
        if (!empty($data['end_date_time'])) {
            $obj_datetime = new DateTime($data['end_date_time']);
            $data['end_date_time'] = $obj_datetime->format('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Set default data for action edit
     * @return array
     */
    protected function setEditFormDefaultValue()
    {
        $data = Mage::registry('dealitems_data')->getData();

        $model_product = Mage::getModel('catalog/product')->load($data['product_id']);
        $model_stock_item = $model_product->getData('stock_item');
        $product_type_array = Mage::getModel('catalog/product_type')->getOptionArray();
        $data['product_type'] = $product_type_array[$model_product->getData('type_id')];
        $data['product_price'] = round($model_product->getData('price'), 2);
        $data['product_qty'] = round($model_stock_item->getData('qty'), 0);

        // Javascript datepicker update fail format : true is ( 2013-04-01 4:00:00 PM ) -> ( 2013-04-01 14:00:00 )
        if (!empty($data['start_date_time'])) {
            $obj_datetime = new DateTime($data['start_date_time']);
            $data['start_date_time'] = $obj_datetime->format('Y-m-d H:i:s');
        }
        if (!empty($data['end_date_time'])) {
            $obj_datetime = new DateTime($data['end_date_time']);
            $data['end_date_time'] = $obj_datetime->format('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Set default data from schedule
     * @return array
     */
    protected function setValuesFromSchedule()
    {
        $data = array();

        $start = $this->getRequest()->getParam('start');
        $data['start_date_time'] = $start;
        $data['end_date_time'] = date('Y-m-d H:i:s', strtotime($start) + 86399);

        return $data;
    }

}