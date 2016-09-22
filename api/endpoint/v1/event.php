<?php

class endpoint_v1_event extends HungryREST {
    public $model_class = 'Event';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $event_last_id; 
    public $event_first_id;
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
        return false;
    }

    function get(){

        $m=$this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();

            return $this->outputOneEvent($o);
            // echo "<pre>";
            // print_r($output);
            // return $output;
            // return $output;
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        
        $unset_key = ['banner_image_id','banner_image','owner_name','detail','mobile_no','phone_no','email','website','facebook_page_url','event_attraction','instagram_page_url','guidelines','how_to_reach','disclaimer'];
        $temp = $this->outputMany($m);
        foreach ($temp as $event) {
            $output[$event['id']] = array_diff_key($this->outputOne($event), array_flip($unset_key));
        }

        $last_model = clone($m);
        $first_model = clone($m);
        $first_id = $last_model->setOrder('id','desc')->tryLoadAny()->id;
        $last_id = $first_model->setOrder('id','asc')->tryLoadAny()->id;

        $next_url = null;
        $previous_url = null;

        if($_GET['type'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city']]);
        }
        elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$first_id,'type'=>"previous",'city'=>$_GET['city']]);
        }else{
            
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city']]);
        }

        // $output['paginator']['next_url'] = $next_url;
        // $output['paginator']['previous_url'] = $previous_url;

        // echo "<pre>";
        // print_r($output);
        return ['events'=>array_values($output),'next_url'=>$next_url,'previous_url'=>$previous_url];
    }

    function outputOneEvent($o){
        $data = $o;
        if($this->model->loaded()){            
            $data = $this->model;
        }
        $output = [];
        
        $output[$data['id']] = $this->outputOne($data);
        $output[$data['id']]['day'] = $data->getDayTime();
        // $output[$data['id']]['ticket'] = $event->ref('Event_Ticket')->getRows(['name','price','detail','offer','applicable_offer_qty','offer_percentage','max_no_to_sale']);
        $output[$data['id']]['image'] = $data->getImage();
        $output[$data['id']]['destination'] = $data->getDestination();
        $output[$data['id']]['restaurant'] = $data->getRestaurant();

        return array_values($output); 
    }

    function _model(){
        $this->validateParams();
        $model = parent::_model();

        // $model->addCondition('starting_date','<=',$this->api->today);
        $model->addCondition('closing_date','>=',$this->api->today);
        $model->setOrder('is_featured','desc');

        if($_GET['city'] != "All")  
        $model->addCondition('city',strtoupper($_GET['city']));

        if($_GET['type'] === "next"){
            $model->addCondition('id','>',$_GET['offset']);

        }elseif($_GET['type'] === "previous"){
            $model->addCondition('id','<',$_GET['offset']);
            $model->setOrder('id','desc');
        }else{
            $offset = 0;
        }

        $this->totalRecord = $model->count()->getOne();
        $model->setLimit($_GET['limit']?:10);

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
        $required_param_variable = ['city','area'];

        if(!$_GET['city'] or is_numeric($_GET['city'])){
            throw new \Exception("some thing wrong ...1001"); //must pass city
        }

        if($_GET['area'] or is_numeric($_GET['area']))
            throw new \Exception("some thing wrong ...1002"); //Area Code

        if(!$_GET['limit'] and !$_GET['id'])
            throw new \Exception("some thing wrong ...1003"); //must pass limit

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...5001", 1); //type must be in array
    }

}