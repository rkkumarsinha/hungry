<?php

class page_adminevent extends Page {

    public $title='Event Management';

    function init() {
        parent::init();

        $this->api->menu->addItem(['Event Category','icon'=>'ajust'],'/eventcategory');
        $this->api->menu->addItem(['Event','icon'=>'ajust'],'/event');
        $this->api->menu->addItem(['User Event','icon'=>'ajust'],'/userevent');
        $this->api->menu->addItem(['Event Voucher','icon'=>'ajust'],'/eventvoucher');
    }
}