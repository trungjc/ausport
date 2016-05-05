<?php

class Glace_Dailydeal_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $uri = explode('/dailydeal', $_SERVER['REQUEST_URI']);
        $uri1 = explode('/dailydeal/index', $_SERVER['REQUEST_URI']);
        $uri2 = explode('/dailydeal/index/index', $_SERVER['REQUEST_URI']);
        
        //if ((sizeof($uri) > 1) || (sizeof($uri1) > 1) || (sizeof($uri2) > 1)) {
            //$link = Mage::helper('dailydeal')->getUrlHttp('dailydeal/index', true);
            //$this->_redirectUrl($link);
            
        //} else {
            $this->loadLayout();
            $this->renderLayout();
        //}
        
    }

}