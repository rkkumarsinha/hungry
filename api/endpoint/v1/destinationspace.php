<?php

class endpoint_v1_destinationspace extends HungryREST {
    public $model_class = 'Destination_Space';
    public $allow_list=true;
    public $allow_list_one=false;
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
            return $this->outputSpace($o);
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        return $this->outputSpace($m);
    }

    function outputSpace(){
        $output = [];
        
        $data = $this->model->_dsql()->group('cps');
        foreach ($data as $m) {
            $output[] = $m['cps'];
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