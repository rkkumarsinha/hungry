<?php

class page_adminrestaurant extends Page {

    public $title='Restaurant Management';

    function init() {
        parent::init();

     	$this->api->menu->addItem(['Category','icon'=>'ajust'],'/category');
     	$this->api->menu->addItem(['Keyword','icon'=>'ajust'],'/keyword');
        $this->api->menu->addItem(['Highlight','icon'=>'ajust'],'/highlight');
        $this->api->menu->addItem(['Discount Coupon','icon'=>'ajust'],'/discountcoupon');
        $this->api->menu->addItem(['Restaurant','icon'=>'ajust'],'/restaurant');
        $this->api->menu->addItem(['Offer','icon'=>'ajust'],'/offer');
        $this->api->menu->addItem(['Discount','icon'=>'ajust'],'/discount');
        $this->api->menu->addItem(['Reserved Table','icon'=>'ajust'],'/reservedtable');
        $this->api->menu->addItem(['Review','icon'=>'ajust'],'/restaurantreview');
    }
}
