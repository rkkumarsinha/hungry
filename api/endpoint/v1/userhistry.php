<?php

class endpoint_v1_userhistry extends HungryREST {
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
        if($this->api->auth->model->id != $this->model->id){
            echo json_encode(array('status'=>'falied','message'=>'authentication failure'));
            exit;
        }

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

        $review = $data->getReview(null,$this->api->today,5);
        
        $review_model_count = $this->add('Model_Review')->addCondition('user_id',$this->api->auth->model->id)->count()->getOne();

        if($review_model_count > 5){
            $url = $this->app->getConfig('apipath').$this->app->url('v1_getreview',['limit'=>10,'offset'=>$review[4]['id'],'type'=>"previous",'for'=>'user']);
            $review['paginator']['next_url'] = $url;
        }

        $discount = $data->getDiscount(null,$this->api->today,$limit=5);
        $dc_model_count = $this->add('Model_DiscountCoupon')->addCondition('user_id',$this->api->auth->model->id)->count()->getOne();
        if($dc_model_count > 5){
            $url = $this->app->getConfig('apipath').$this->app->url('v1_getdiscountcoupon',['limit'=>10,'offset'=>$discount[4]['id'],'type'=>"previous",'for'=>'user']);
            $discount['paginator']['next_url'] = $url;
        }

        $reserve_table = $data->getReserveTable(null,$this->api->today,5);
        $rt_model_count = $this->add('Model_ReservedTable')->addCondition('user_id',$this->api->auth->model->id)->count()->getOne();
        if($rt_model_count > 5){
            $url = $this->app->getConfig('apipath').$this->app->url('v1_getreservetable',['limit'=>10,'offset'=>$reserve_table[4]['id'],'type'=>"previous",'for'=>'user']);
            $reserve_table['paginator']['next_url'] = $url;
        }

        $event_ticket = $data->getEventTicket(null,$this->api->today,5);
        $et_model_count = $this->add('Model_UserEventTicket')->addCondition('user_id',$this->api->auth->model->id)->count()->getOne();
        
        if($et_model_count > 5){
            $url = $this->app->getConfig('apipath').$this->app->url('v1_geteventticket',['limit'=>10,'offset'=>$event_ticket[4]['id'],'type'=>"next",'for'=>'user']);
            $event_ticket['paginator']['next_url'] = $url;
        }

        $output[] = [
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'created_at'=>$data['created_at'],
                    'mobile'=>$data['mobile'],
                    'image'=>$data['image'],
                    'dob'=>$data['dob'],
                    'referral_code'=>$data['referral_code'],
                    'review'=>array_values($review),
                    'discount'=>array_values($discount),
                    'reserve_table'=>array_values($reserve_table),
                    'event_ticket'=>array_values($event_ticket)
                ];
        // echo "<pre>";
        // print_r($output);
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