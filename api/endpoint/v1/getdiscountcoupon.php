<?php

class endpoint_v1_getdiscountcoupon extends HungryREST {
    public $model_class = 'DiscountCoupon';
    public $allow_list=true;
    public $allow_list_one=false;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $totalRecord = 0;

    function init(){
        parent::init();

        //check authorization here for second time
    }

    function authenticate(){
        $data = parent::authenticate();
        if($data['status'] === "success")
            return true;

        echo json_encode($data);
        exit;
    }

    function get(){

        $m = $this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            return "list one not allowed";
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        
        // $output = $this->outputMany($m);
        $output = [];
        $count = 1;
        $first_id = 0;
        $last_id = 0;

        foreach ($m as $model) {
            if($count===1)
                $first_id = $model->id;

            $output[$model->id] = [
                                'id'=>$model->id,
                                'user_id'=>$model['user_id'],
                                'user'=>$model['user'],
                                'restaurant_id'=>$model['restaurant_id'],
                                'restaurant'=>$model['restaurant'],
                                'discount_id'=>$model['discount_id'],
                                'discount'=>$model['discount'],
                                'offer_id'=>$model['offer_id'],
                                'offer'=>$model['offer'],
                                'name'=>$model['name'],
                                'email'=>$model['email'],
                                'mobile'=>$model['mobile'],
                                'created_at'=>$model['created_at'],
                                'discount_coupon'=>$model['discount_coupon'],
                                'discount_taken'=>$model['discount_taken'],
                                'status'=>$model['status'],
                                'total_amount'=>$model['total_amount'],
                                'amount_paid'=>$model['amount_paid'],
                                'payment_mode'=>$model['payment_mode'],
                                'restaurant_address'=>$model['restaurant_address'],
                                'restaurant_image'=>$model['restaurant_image'],
                                'created_date'=>$model['created_date'],
                                'restaurant_name'=>$model['restaurant_name']
                            ];

            $last_id = $model->id;
            $count++;
        }
            
        // var_dump($first_id);
        // var_dump($last_id);
        // exit;
        $data = ['list'=>array_values($output)];

        $next_url = null;
        $previous_url = null;

        $next_offset = $_GET['offset'] + $_GET['limit'];
        $previous_offset = $_GET['offset'] - $_GET['limit'];
        if($previous_offset < 0){
            $previous_offset = 0;
        }

        if($_GET['type'] === "next"){
            if($this->totalRecord > $_GET['offset'])
               $next_url  = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }else{
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }

        $data['next_url'] = $next_url;
        $data['previous_url'] = $previous_url;
        // echo "<pre>";
        // print_r($output);
        // exit;
        return $data;
    }

    function _model(){
        $this->validateParams();
        
        $model = parent::_model();

        switch ($_GET['for']) {
            case 'user':
                $model->addCondition('user_id',$this->api->auth->model->id);
                break;
            case 'restaurant':
                $model->addCondition('restaurant_id',$_GET['restaurant_id']);
                break;
        }

        // if($_GET['type'] === "next"){
        //     $model->addCondition('id','>',$_GET['offset']);
        // }elseif($_GET['type'] === "previous"){
        //     $model->addCondition('id','<',$_GET['offset']);
        //     $model->setOrder('id','desc');
        // }else{
        //     $offset = 0;
        // }

        $offset = 0;
        if($_GET['offset'] > 0)
            $offset = $_GET['offset'];

        $this->totalRecord = $model->count()->getOne();
        if($_GET['limit']){
            $model->setLimit($_GET['limit'],$offset);
        }

        if($this->totalRecord == 0){
            echo "no record found";
            exit;
        }
        return $model;
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

    private function validateParams(){

        if(!$_GET['limit'])
            throw new \Exception("some thing wrong ...1001"); //must pass limit

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...1002", 1); //type must be in array

        if(!$_GET['for'] or !in_array($_GET['for'],['user','restaurant']))
            throw new \Exception("some thing wrong...1003", 1);

        if($_GET['for'] === "restaurant"){
            if(!$_GET['restaurant_id'])
                throw new \Exception("some thing wrong...1004, rid");
        }
    }
}