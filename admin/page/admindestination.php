<?php

class page_admindestination extends Page {

    public $title='Destination Management';

    function init() {
        parent::init();

        $this->app->menu->addItem(['Venue','icon'=>'ajust'],'/venue');
        $this->app->menu->addItem(['Destination','icon'=>'ajust'],'/destination');
        $this->app->menu->addItem(['Highlight','icon'=>'ajust'],'/destination_highlight');
        $this->app->menu->addItem(['Review','icon'=>'ajust'],'/destinationreview');
        $this->app->menu->addItem(['Enquiry','icon'=>'ajust'],'/destinationenquiry');
        
    }
}
