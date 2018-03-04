<?php

class endpoint_v1_version extends HungryREST {
    public $allow_list=false;
    public $allow_list_one=false;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){
    	parent::init();

    }

    function authenticate(){
        $data = parent::authenticate();
        if($data['status'] === "success")
            return true;

        echo json_encode($data);
        exit;
        return false;
    }

    function get(){
        return $this->app->getConfig('app_version');
    }
}