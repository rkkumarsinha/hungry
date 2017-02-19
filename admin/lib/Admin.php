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
        try{
            $auth->usePasswordEncryption();
            $user_model = $this->add('Model_User')->addCondition('type','superadmin');
            $auth->setModel($user_model,'email','password');
            $auth->check();
        }catch(Exception $e){
            $this->js(true)->univ()->errorMessage('authentication error')->execute();
            // exit;
        }

        if($this->api->auth->model['type'] != "superadmin"){
            $this->api->auth->logout();
            exit;
        }

        $this->api->today = date('Y-m-d');


        // $this->js(true)->_load("selectize");
        $this->api->jui->addStaticInclude('ckeditor/ckeditor');
        $this->api->jui->addStaticInclude('ckeditor/adapters/jquery');
    }

    function initTopMenu(){

        $top_menu=$this->layout->add('Menu_Horizontal',null,'Top_Menu');
        $top_menu->addItem(['Restaurant','icon'=>'ajust'],'/adminrestaurant');
        $top_menu->addItem(['Destination','icon'=>'ajust'],'/admindestination');
        $top_menu->addItem(['Event','icon'=>'ajust'],'/adminevent');
        $top_menu->addItem(['Configuration','icon'=>'ajust'],'/adminconfiguration');
        $top_menu->addItem(['Notification','icon'=>'ajust'],'/notification');
        $top_menu->addItem(['Verification','icon'=>'ajust'],'/verify');
        $top_menu->addItem(['Subscriber','icon'=>'ajust'],'/subscriber');
        $top_menu->addItem(['User','icon'=>'users'],'/user');
        $top_menu->addItem(['Enquiry','icon'=>'users'],'/enquiry');
        $top_menu->addItem(['Report','icon'=>'ajust'],'/report');
            
    }
}