<?php

class page_cartdetail extends Page{
    function init(){
        parent::init();
      
      $cart = $this->add('View_CartDetail')->setStyle(array('margin-top'=>"10px",'margin-bottom'=>"10px"));
      $cart->setModel($this->add('Model_Cart'));
  }
}