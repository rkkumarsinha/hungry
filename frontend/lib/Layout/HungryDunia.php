<?php
/**
 * Centered layout for showing a single form or message on the page
 */
class Layout_HungryDunia extends Layout_Basic {

	function init(){
		parent::init();

		// $cart = $this->add('Model_Cart');
		// $count = $cart->getEventCount();

		// if($count){
		// 	$this->template->set('event_count',$count);
		// }else{
		// 	$this->template->tryDel('event_cart_wrapper');
		// }

	}

    function defaultTemplate() {
        return array('layout/hungrydunia');
    }

}
