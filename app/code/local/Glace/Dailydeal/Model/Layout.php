<?php

class Glace_Dailydeal_Model_Layout extends Mage_Core_Model_Abstract
{

    /**
     * @return Glace_Dailydeal_Model_Layout
     */
    public static function getInstance()
    {
        return Mage::getSingleton('dailydeal/layout');
    }

    /**
     * @return Glace_Dailydeal_Model_Layout
     */
    public static function getModel()
    {
        return Mage::getModel('dailydeal/layout');
    }

    public static function overrideProductView($observer)
    {
        if( !Glace_Dailydeal_Helper_Data::getConfigOption() || !Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled() ){
            return;
        }
        
        $action = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();

        if ($action->getRequest()->getControllerName() == 'product'
                && $action->getRequest()->getActionName() == 'view') {
            $layout->getUpdate()->addUpdate('
                <reference name="product.info">
                    <action method="setTemplate"><template>glace_dailydeal/catalog/product/view.phtml</template></action>
                </reference>
            ');
            $layout->generateXml();
        }
    }

}