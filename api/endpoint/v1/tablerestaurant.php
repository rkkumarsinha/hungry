<?php

class endpoint_v1_tablerestaurant extends HungryREST{
    public $model_class = 'Restaurant';
    public $allow_list=true;
    public $allow_list_one=false;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){ // at third step 
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

    function get(){ //always at last
        //check for the area id
        $m=$this->model;

        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {            
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            throw new \Exception("some thing wrong", 1);
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        $output['restaurants'] = $this->outputMany($m);

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

        $output['next_url'] = $next_url;
        $output['previous_url'] = $previous_url;

        // echo "<pre>";
        // print_r($output);
        return $output;
    }

    function _model(){ //always second step
        $this->validateParam();

        $model =  parent::_model();

        $model->addCondition('status','active');
        $model->addCondition('is_verified',true);

        $model->addCondition('reservation_needed',true);
        $model->setOrder('rating','desc');
        $model->setOrder('is_featured','desc');
        $model->setOrder('is_recommend','desc');
        $model->setOrder('is_popular','desc');
        
        $city_model = $this->add('Model_City')->addCondition('name',$_GET['city']);
        $city_model->tryLoadAny();
        if(!$city_model->loaded())
            throw new \Exception("Error Processing Request 1005");

        if($_GET['city'])
            $model->addCondition('city_id',$city_model->id);

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

        return $model;
        // return $model->addCondition('status','active');
        
    }

        /**
     * Generic outptu filtering method for multiple records of data
     *
     * @param [type] $data [description]
     *
     * @return [type] [description]
     */
    protected function outputMany($data)
    {
        
        if(is_object($data))
            $data=$data->getRows();

        $output = array();
        foreach ($data as $row) {
            $output[$row['id']] = $this->outputOne($row);
            $output[$row['id']]['offer_count'] = $output[$row['id']]['offers'];
            unset($output[$row['id']]['offers']);
            unset($output[$row['id']]['discounts']);
            //get offers
            $offer_asso = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$row['id'])
                            ->addCondition('is_active',true)
                            ;

            $offers_temp = [];
            foreach ($offer_asso as $temp) {
                $offers_temp[] = ['id'=>$temp['offer_id'],'name'=>$temp['name'],'detail'=>$temp['detail']];
            }
            $output[$row['id']]['restaurant_offers'] = $offers_temp;

        }

        return array_values($output); 
    }

    function put($data){
        // return json_encode($data);
        return "you are not allow to access";
    }

    function delete($data){
        return "you are not allow to access";   
    }

    function validateParam(){
        if(count($_GET) > 5)
            throw new \Exception("Error Processing Request 1001");

        if(!is_numeric($_GET['limit']) or $_GET['limit'] > 20)
            throw new \Exception("Error Processing Request 1003");

        if(!$_GET['city'])
            throw new \Exception("Error Processing Request 1004");

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...5001", 1); //type must be in array
    }

}