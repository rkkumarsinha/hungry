<?php

class endpoint_v1_keyword extends HungryREST {
    public $model_class = 'Restaurant_Keyword';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $rest_last_id; 
    public $rest_first_id;
    public $totalRecord = 0;
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
            return $this->outputOne($o);
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        return $this->outputManyRestaurant($m);
    }

    function outputManyRestaurant(){
        $data = $this->model;
        $output = [];
        $rest_recommend = [];
        $last_id = 0;
        $first_id = 0;
        $count = 1;
        foreach ($data as $keyword_asso) {
            if($count==1)
                $first_id = $keyword_asso['id'];

            $rest = $this->add('Model_Restaurant')
                    ->addCondition('id',$keyword_asso['restaurant_id'])
                    ->addCondition('status','active')
                    ->addCondition('is_verified',true)
                    ->setOrder('is_recommend','desc')
                    ->tryLoadAny()
                    ;
            
            if(!$rest->loaded())
                continue;
            
            $rest_data['id'] = $rest['id'];
            $rest_data['name'] = $rest['name'];
            $rest_data['address'] = $rest['address'];

            $rest_data['longitude'] = $rest['longitude'];
            $rest_data['latitude'] = $rest['latitude'];
            $rest_data['logo_image'] = $rest['logo_image'];
            $rest_data['display_image'] = $rest['display_image'];
            $rest_data['banner_image'] = $rest['banner_image'];

            $rest_data['address'] = $rest['address'];
            $rest_data['discount'] = ($rest['discount_percentage'] - $rest['discount_subtract'])?($rest['discount_percentage'] - $rest['discount_subtract']):0;
            $rest_data['discount_id'] = $rest['discount_id'];
            // $rest_data['mobile_no'] = $rest['mobile_no'];
            // $rest_data['email'] = $rest['email'];
            $rest_data['avg_cost_per_person_veg'] = $rest['avg_cost_per_person_veg'];
            $rest_data['avg_cost_per_person_nonveg'] = $rest['avg_cost_per_person_nonveg'];
            $rest_data['avg_cost_per_person_thali'] = $rest['avg_cost_per_person_thali'];
            $rest_data['avg_cost_of_a_beer'] = $rest['avg_cost_of_a_beer'];
            $rest_data['food_type'] = $rest['food_type'];
            $rest_data['offer_count'] = $rest['offers'];
            $rest_data['rating'] = $rest['rating'];

            //get offers
            $offer_asso = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$rest['id'])
                            ->addCondition('is_active',true)
                            ;

            $offers_temp = [];
            foreach ($offer_asso as $temp) {
                $offers_temp[] = ['id'=>$temp['id'],'name'=>$temp['name'],'detail'=>$temp['detail']];
            }
            $rest_data['restaurant_offers'] = $offers_temp;

            $last_id = $rest['id'];

            // if($rest['is_recommend'])
            //     $rest_recommend[] = $rest_data;
            // else
                $output[] =  $rest_data;

            $count++;
        }
                
        $data_out['restaurants'] = $output;
        // $data_out['recommend'] = $rest_recommend;
        
        $next_url = null;
        $previous_url = null;

        if($_GET['type'] === "next"){            
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
        }
        elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$first_id,'type'=>"previous",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
        }else{
            
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"next",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'type'=>"previous",'city'=>$_GET['city'],'keyword'=>$_GET['keyword']]);
        }

        $data_out['next_url'] = $next_url;
        $data_out['previous_url'] = $previous_url;

        return $data_out;
    }

    function _model(){
        $this->validateParams();
        $model = parent::_model(); 

        $model->addExpression('city')->set(function($m,$q){
            return $q->expr('UPPER([0])',[$m->refSQL('restaurant_id')->fieldQuery('city')]);
        });

        // $model->addExpression('keyword_name')->set(function($m,$q){
        //     return $q->expr('UPPER([0])',[$m->refSQL('keyword_id')->fieldQuery('name')]);
        // });

        $model->addCondition('city',strtoupper($_GET['city']));
        $model->addCondition('keyword_id',$_GET['keyword']);

        if($_GET['type'] === "next"){
            $model->addCondition('id','>',$_GET['offset']);

        }elseif($_GET['type'] === "previous"){
            $model->addCondition('id','<',$_GET['offset']);
            $model->setOrder('id','desc');
        }else{
            $offset = 0;
        }
        
        $this->totalRecord = $model->count()->getOne();
        $model->setLimit($_GET['limit']);

        if($model->count()->getOne() == 0){
            echo "no record found";
            exit;
        }
        
        // $model->tryLoadAny();
        // $model->addExpression();
        //apply all condition here

        // $this->rest_first_id = $model->setOrder('id','asc')->setLimit(1)->id;
        // $this->rest_last_id = $model->setOrder('id','desc')->setLimit(1)->id;
        
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
        $required_param_variable = ['city','keyword'];

        if(!$_GET['city'] or is_numeric($_GET['city']))
            throw new \Exception("some thing wrong ...1001"); //must pass city

        if(!$_GET['keyword'] or !is_numeric($_GET['keyword']))
            throw new \Exception("some thing wrong ...1002"); //must pass category

        if(!is_numeric($_GET['limit']))
            throw new \Exception("some thing wrong ...1003"); //must pass limit

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...5001", 1); //type must be in array
            
    }

}