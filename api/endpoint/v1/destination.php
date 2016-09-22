<?php

class endpoint_v1_destination extends HungryREST {
    public $model_class = 'Destination';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $event_last_id; 
    public $event_first_id;

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

        $m=$this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();

            $o['space'] = $m->getSpace();
            $o['package'] = $m->getPackage();
            $o['facility'] = $m->getFacility();
            $o['occassion'] = $m->getOccassion();
            $o['service'] = $m->getService();
            $o['venue'] = $m->getVenue();
            $o['gallary'] = $m->getGallary();
            $o['review'] = $m->getReview();
            $o['events'] = $m->getEvent();
            return $this->outputOne($o);
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        return $this->outputManyEvent($m);
    }

    function outputManyEvent(){

        $data = $this->model;
        $output = [];
        $rest_upcoming = [];
        $last_id = 0;
        $first_id = 0;
        $count = 1;

        $output = [];
        foreach ($data as $destination) {
            
            if($count==1)
                $first_id = $destination['id'];

            $unset_key = ['user_id','user','logo_image_id','banner_image','banner_image_id','display_image_id','owner_name','about_destination','mobile_no','phone_no','email','facebook_page_url','instagram_page_url','credit_card_accepted','created_at','updated_at','monday','tuesday','wednesday','thursday','friday','saturday','sunday','guidelines','cancellation_policy','booking_policy','payment_method','how_to_reach'];

            $output[$destination['id']] = array_diff_key($this->outputOne($destination), array_flip($unset_key));
            // $output[$destination['id']]['space'] = $destination->getSpace();
             $output[$destination['id']]['package'] = $destination->getPackage();
            // $output[$destination['id']]['facility'] = $destination->getFacility();
            // $output[$destination['id']]['occassion'] = $destination->getOccassion();
            // $output[$destination['id']]['service'] = $destination->getService();
             $output[$destination['id']]['venue'] = $destination->getVenue();
            // $output[$destination['id']]['gallary'] = $destination->getGallary();
            // $output[$destination['id']]['review'] = $destination->getReview();
            // $output[$destination['id']]['events'] = $destination->getEvent();

            $last_id = $destination['id'];
            $count++;
        }

        // echo "<pre>";
        // print_r( $output);
        $param_array = [];
        if($_GET['city']){
            // $this->api->stickyGET('city');
            $param_array['city'] = $_GET['city'];
        }
        if($_GET['venue'])
            $param_array['venue'] = $_GET['venue'];
        // $this->api->stickyGET('venue');

        if($_GET['area'])
            $param_array['area'] = $_GET['area'];
        // $this->api->stickyGET('area');

        if($_GET['limit'])
            $param_array['limit'] = $_GET['limit'];
            // $this->api->stickyGET('limit');
        
        if($_GET['cps'])
            $param_array['cps'] = $_GET['cps'];
        // $this->app->stickyGET('cps');
        if($_GET['min_price'])
            $param_array['min_price'] = $_GET['min_price'];
        // $this->app->stickyGET('min_price');

        if($_GET['max_price'])
            $param_array['max_price'] = $_GET['max_price'];
        // $this->app->stickyGET('max_price');

        // $this->app->stickyGET('last_id');
        // $this->app->stickyGET('type');
        $next_url = null;
        $previous_url = null;

        if($_GET['type'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']],$param_array));
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city']],$param_array));
        }
        elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']],$param_array));
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$first_id,'type'=>"previous",'city'=>$_GET['city']],$param_array));
        }else{
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city']],$param_array));
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,array_merge(['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city']],$param_array));
        }

        // $output['next_url'] = $next_url;
        // $output['previous_url'] = $previous_url;

        return ['destinations'=>array_values($output),'next_url'=>$next_url,'previous_url'=>$previous_url];
    }

    function _model(){
        $this->validateParams();
        $model = parent::_model(); 

        $model->addCondition('city_id',strtoupper($_GET['city']));
        $asso_j = $model->join('destination_venue_association.destination_id','id');
        $asso_j->addField('venue_id');
        $venue_j = $asso_j->join('venue.id','venue_id');
        $venue_j->addField('venue_name','name');
        $model->addCondition('venue_name',$_GET['venue']);

        $model->setOrder('rating','desc');
        $model->setOrder('is_featured','desc');
        $model->setOrder('is_recommend','desc');
        $model->setOrder('is_popular','desc');
        
        if($_GET['cps']){
            $model->addExpression('min_cps')->set($model->refSQL('Destination_Space')->setOrder('cps','asc')->setLimit(1)->fieldQuery('cps'));
            $model->addExpression('max_cps')->set($model->refSQL('Destination_Space')->setOrder('cps','desc')->setLimit(1)->fieldQuery('cps'));
            $model->addCondition('min_cps',"<=",$_GET['cps']);
            $model->addCondition('max_cps',">=",$_GET['cps']);
        }

        if($_GET['min_price']){
            $model->addExpression('min_price')->set($model->refSQL('Destination_Package')->setOrder('price','asc')->setLimit(1)->fieldQuery('price'));
            $model->addCondition('min_price',"<=",$_GET['min_price']);
        }

        if($_GET['max_price']){
            $model->addExpression('max_price')->set($model->refSQL('Destination_Package')->setOrder('price','desc')->setLimit(1)->fieldQuery('price'));
            $model->addCondition('max_price',">=",$_GET['max_price']);   
        }

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

        if(!$_GET['city'] or !is_numeric($_GET['city'])){
            throw new \Exception("some thing wrong ...1001"); //must pass city
        }

        if(!$_GET['venue'] or is_numeric($_GET['venue'])){
            throw new \Exception("some thing wrong ...1003"); //must pass city
        }

        if($_GET['area'] or is_numeric($_GET['area']))
            throw new \Exception("some thing wrong ...1002"); //Area Code

        if(!$_GET['limit'] and !$_GET['id']){
            throw new \Exception("some thing wrong ...1003"); //must pass limit
        }

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...5001", 1); //type must be in array

        if($_GET['cps'] and !(is_numeric($_GET['cps'])))
            throw new \Exception("some thing wrong...6001", 1);

        if($_GET['min_price'] and !(is_numeric($_GET['min_price'])))
            throw new \Exception("some thing wrong...7001", 1);
        
        if($_GET['max_price'] and !(is_numeric($_GET['max_price'])))
            throw new \Exception("some thing wrong...8001", 1);


    }

}