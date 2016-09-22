<?php

class endpoint_v1_user extends HungryREST {
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

        if($m['status'] != 'active'){
            return ["user not active"];
        }

        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {            
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            return $this->outputUser($o);
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        throw new \Exception("Some thing wrong");
        
    }

    function outputUser(){
        $data = $this->model;
        $output = [];
        $output[] = [
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'created_at'=>$data['created_at'],
                    'mobile'=>$data['mobile'],
                    'dob'=>$data['dob'],
                    'image'=>$data['image'],
                    'referral_code'=>$data['referral_code'],
                    'review'=>$data->getReview($this->api->today),
                    'discount'=>$data->getDiscount($this->api->today),
                    'reserve_table'=>$data->getReserveTable($this->api->today),
                    'event_ticket'=>$data->getEventTicket($this->api->today)
                ];
        return $output;
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