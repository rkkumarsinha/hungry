<?php

class endpoint_v1_authenticate extends Endpoint_REST {
    public $model_class = 'User';
    public $allow_list=false;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){
    	parent::init();
    }

    function authenticate(){
        
        return true;
    }

    function get(){
        $m=$this->model;

        $m->tryLoadAny();
        if(!$m->loaded()){
            return ["user not active"];
        }

        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {            
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            return "Not Allowed";
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        throw new \Exception("Some thing wrong");
        
    }

    function _model(){
        $model =  parent::_model();
        return $model;
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

}