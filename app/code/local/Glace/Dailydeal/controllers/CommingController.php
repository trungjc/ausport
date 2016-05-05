<?php

class Glace_Dailydeal_CommingController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $uri = explode('/dailydeal/comming', $_SERVER['REQUEST_URI']);
        $uri1 = explode('/dailydeal/comming/index', $_SERVER['REQUEST_URI']);

        //if ((sizeof($uri) > 1) || (sizeof($uri1) > 1)) {
            //$link = Mage::helper('dailydeal')->getUrlHttp('dailydeal/comming', true);
            //$this->_redirectUrl($link);
        //} else {
            $this->loadLayout();
            $this->renderLayout();
        //}
    }

}