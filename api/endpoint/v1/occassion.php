<?php

class endpoint_v1_occassion extends HungryREST {
    public $model_class = 'Destination_Occasion';
    public $allow_list=true;
    public $allow_list_one=true;
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
            $output[] = ['name'=>$m['name'],'id'=>$m['id'],'image'=>$m['image']];
        }        
        return $output;

    }


    function _model(){
        return parent::_model()->addCondition('is_active',true)->addCondition('type',"occasion");
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

}