<?php

/**
    Return 1: json of category list
    Return 2: one Category detail with image and required param is id
*/

class endpoint_v1_categorylist extends HungryREST {
    public $model_class = 'Category';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){
    	parent::init();
    	// throw new \Exception(print_r($_GET));        
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
        //check for the area id
        $m=$this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {            
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            return $this->outputOne($o);
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        return $this->outputManyHighlight();
    }

    function outputManyHighlight(){
        $data = $this->model;
        $output = [];

        foreach ($data as $m) {
            $output[] = ['name'=>$m['name'],'id'=>$m['id']];
        }        
        return $output;

    }


    function _model(){
        return parent::_model();
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

}