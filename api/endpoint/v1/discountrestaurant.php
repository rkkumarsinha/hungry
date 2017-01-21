<?php

class endpoint_v1_discountrestaurant extends HungryREST {
    public $model_class = 'Restaurant';
    public $allow_list=true;
    public $allow_list_one=false;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $totalRecord = 0;
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

        // $output = $this->outputMany($m);
        $output['restaurants'] = $this->outputMany($m);

        // $last_model = clone($m);
        // $first_model = clone($m);
        // $first_id = $last_model->setOrder('id','desc')->tryLoadAny()->id;
        // $last_id = $first_model->setOrder('id','asc')->tryLoadAny()->id;

        $next_url = null;
        $previous_url = null;

        $next_offset = $_GET['offset'] + $_GET['limit'];
        $previous_offset = $_GET['offset'] - $_GET['limit'];
        if($previous_offset < 0){
            $previous_offset = 0;
        }

        if($_GET['scroll'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'scroll'=>"next",'city'=>$_GET['city'],'type'=>$_GET['type']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'scroll'=>"previous",'city'=>$_GET['city'],'type'=>$_GET['type']]);
        }
        elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'scroll'=>"next",'city'=>$_GET['city'],'type'=>$_GET['type']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'scroll'=>"previous",'city'=>$_GET['city'],'type'=>$_GET['type']]);
        }else{
            
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'scroll'=>"next",'city'=>$_GET['city'],'type'=>$_GET['type']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'scroll'=>"previous",'city'=>$_GET['city'],'type'=>$_GET['type']]);
        }

        $output['previous_url'] = $previous_url;
        $output['next_url'] = $next_url;
        return $output;
    }

    function _model(){ //always second step
        $this->validateParam();

        $model =  parent::_model();

        $city_model = $this->add('Model_City')->addCondition('name',$_GET['city']);
        $city_model->tryLoadAny();
        if(!$city_model->loaded())
            throw new \Exception("Error Processing Request 1005");
            
        // $model->addExpression('city_name')->set($model->refSQL('city_id')->fieldQuery('name'));
        $model->addCondition('status','active');
        $model->addCondition('is_verified',true);

        if($_GET['type']=="discount")
            $model->addCondition('discount_id','<>',null);

        if($_GET['type'] == "offer")
            $model->addCondition('offers','>',0);

        if($_GET['city'])
            $model->addCondition('city_id',$city_model->id);

        // if($_GET['scroll'] === "next"){
        //     $model->addCondition('id','>',$_GET['offset']);

        // }elseif($_GET['scroll'] === "previous"){
        //     $model->addCondition('id','<',$_GET['offset']);
        //     $model->setOrder('id','desc');
        // }else{
        //     $offset = 0;
        // }
        $offset = 0;
        if($_GET['offset'] > 0)
            $offset = $_GET['offset'];

        $this->totalRecord = $model->count()->getOne();
        $model->setLimit($_GET['limit'],$offset);
        
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
            $output[$row['id']]['rating'] = round($output[$row['id']]['rating'],1);
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
                $offers_temp[] = ['id'=>$temp['id'],'name'=>$temp['name'],'detail'=>$temp['detail']];
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
        
        if(count($_GET) > 6)
            throw new \Exception("Error Processing Request 1001");
                
        if(!in_array($_GET['type'],array('offer','discount')))
            throw new \Exception("Error Processing Request 1002");

        if(!is_numeric($_GET['limit']) or $_GET['limit'] > 20)
            throw new \Exception("Error Processing Request 1003");

        if(!$_GET['city'])
            throw new \Exception("Error Processing Request 1004");

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['scroll'] and !in_array($_GET['scroll'], array('next','previous')))
            throw new \Exception("some thing wrong...5001", 1); //type must be in array

    }

}