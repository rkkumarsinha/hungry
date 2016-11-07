<?php

class Admin extends App_Admin {

    function init() {
        parent::init();

        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('addons', 'vendor')
            ))
            ->setBasePath($this->pathfinder->base_location->getPath() . '/..')
        ;    
        
        date_default_timezone_set("Asia/Calcutta");

        $this->dbConnect();
        $auth=$this->add('Auth');
        $auth->usePasswordEncryption();
        $auth->setModel('User','email','password');
        $auth->check();       

        $this->api->today = date('Y-m-d');
        $this->api->menu->addItem(['Event','icon'=>'ajust'],'/event');
        $this->api->menu->addItem(['Event Category','icon'=>'ajust'],'/eventcategory');
        $this->api->menu->addItem(['Category','icon'=>'ajust'],'/category');
        $this->api->menu->addItem(['Country','icon'=>'ajust'],'/country');
        $this->api->menu->addItem(['State','icon'=>'ajust'],'/state');
        $this->api->menu->addItem(['City','icon'=>'ajust'],'/city');
        $this->api->menu->addItem(['Area','icon'=>'ajust'],'/area');
        $this->api->menu->addItem(['Keyword','icon'=>'ajust'],'/keyword');
        $this->api->menu->addItem(['Highlight','icon'=>'ajust'],'/highlight');
        $this->api->menu->addItem(['Offer','icon'=>'ajust'],'/offer');
        $this->api->menu->addItem(['Discount','icon'=>'ajust'],'/discount');
        $this->api->menu->addItem(['Restaurant','icon'=>'ajust'],'/restaurant');
        $this->api->menu->addItem(['Subscriber','icon'=>'ajust'],'/subscriber');
        $this->api->menu->addItem(['Discount Coupon','icon'=>'ajust'],'/discountcoupon');
        $this->api->menu->addItem(['User','icon'=>'users'],'/user');
        $this->api->menu->addItem(['User Reviews','icon'=>'users'],'/review');
        $this->api->menu->addItem(['Configuration','icon'=>'cog'],'/configuration');
        $this->api->menu->addItem(['cancleregion','icon'=>'cog'],'/cancleregion');


        // $this->js(true)->_load("selectize");
        $this->api->jui->addStaticInclude('ckeditor/ckeditor');
        $this->api->jui->addStaticInclude('ckeditor/adapters/jquery');
    }

    function initTopMenu(){

        $top_menu=$this->layout->add('Menu_Horizontal',null,'Top_Menu');
        $top_menu->addItem(['Venue','icon'=>'ajust'],'/venue');
        $top_menu->addItem(['Destination','icon'=>'ajust'],'/destination');
        $top_menu->addItem(['Highlight','icon'=>'ajust'],'/destination_highlight');
        $top_menu->addItem(['User Event','icon'=>'ajust'],'/userevent');
        $top_menu->addItem(['Reserved Table','icon'=>'ajust'],'/reservedtable');
        $top_menu->addItem(['Email Template','icon'=>'ajust'],'/emailtemplate');
        $top_menu->addItem(['Verification','icon'=>'ajust'],'/verify');
        $top_menu->addItem(['Notification','icon'=>'ajust'],'/notification');
        $top_menu->addItem(['TNC','icon'=>'ajust'],'/tnc');
    }
}



        // For improved compatibility with Older Toolkit. See Documentation.
        // $this->add('Controller_Compat42')
        //     ->useOldTemplateTags()
        //     ->useOldStyle()
        //     ->useSMLite();
