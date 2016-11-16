<?php

class endpoint_v1_getreview extends HungryREST {
    public $model_class = 'Review';
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
                                'restaurant_id'=>$model['restaurant_id'],
                                'restaurant'=>$model['restaurant'],
                                'destination_id'=>$model['destination_id'],
                                'destination'=>$model['destination'],
                                'user_profile'=>$model['user_profile'],
                                'rating'=>$model['rating'],
                                'title'=>$model['title'],
                                'comment'=>$model['comment'],
                                'created_at'=>$model['created_at'],
                                'created_time'=>date("H:i:s",strtotime($model['created_time'])),
                                'is_approved'=>$model['is_approved']
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

        if($_GET['type'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'for'=>$_GET['for']]);
        }elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$first_id,'type'=>"next",'for'=>$_GET['for']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'for'=>$_GET['for']]);
        }else{
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'for'=>$_GET['for']]);
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
            case 'destination':
                $model->addCondition('destination_id',$_GET['destination_id']);
                break;
        }

        $model->addCondition('is_approved',true);

        if($_GET['type'] === "next"){
            $model->addCondition('id','>',$_GET['offset']);
        }elseif($_GET['type'] === "previous"){
            $model->addCondition('id','<',$_GET['offset']);
            $model->setOrder('id','desc');
        }else{
            $offset = 0;
        }

        $this->totalRecord = $model->count()->getOne();
        if($_GET['limit']){
            $model->setLimit($_GET['limit']);
        }

        if($model->count()->getOne() == 0){
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

        if(!$_GET['for'] or !in_array($_GET['for'],['user','restaurant','destination']))
            throw new \Exception("some thing wrong...1003", 1);

        if($_GET['for'] === "restaurant"){
            if(!$_GET['restaurant_id'])
                throw new \Exception("some thing wrong...1004, rid");
        }

        if($_GET['for'] === "destination"){
            if(!$_GET['destination_id'])
                throw new \Exception("some thing wrong...1004, did");
        }
    }
}