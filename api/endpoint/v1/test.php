<?php

class endpoint_v1_test extends HungryREST {
    public $model_class = 'Restaurant';
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
            $output[] = ['name'=>$m['name'],'id'=>$m['id']];
        }
        return $output;
    }

    function _model(){
        $model = parent::_model();

        if($_GET['lat'] AND $_GET['lng']){
            $current_lat = $_GET['lat'];
            $current_long = $_GET['lng'];
            $q = $model->dsql();
            $latlng = $q->expr('ABS(ABS([0] - [1]) + ABS([2] - [3]))',[$model->getElement('latitude'),$current_lat,$model->getElement('longitude'),$current_long]);
            $model->addCondition($latlng,"<=",10);
            $model->setOrder($latlng,'asc');
        }

        if($_GET['rating']){
            $model->addCondition('rating',">=",$_GET['rating']);
            $model->setOrder('rating','asc');
        }

        $model->setLimit(20);
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