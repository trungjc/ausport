<?php

class Glace_Dailydeal_PastController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $uri = explode('/dailydeal/past', $_SERVER['REQUEST_URI']);
        $uri1 = explode('/dailydeal/past/index', $_SERVER['REQUEST_URI']);

        ///if ((sizeof($uri) > 1) || (sizeof($uri1) > 1)) {
            //$link = Mage::helper('dailydeal')->getUrlHttp('dailydeal/past', true);
            //$this->_redirectUrl($link);
        //} else {
            $this->loadLayout();
            $this->renderLayout();
        //}
    }

}