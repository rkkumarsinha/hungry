<?php
    
/**
    return list of restaurant and recommed restaurant based on type discount or  offers

*/
class endpoint_v1_discount extends HungryREST {
    public $model_class = 'Discount';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $rest_last_id; 
    public $rest_first_id;
    public $display_restaurant = false;
    public $totalRecord=0;

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

        if($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
                                    
            
            return $this->outputOne($o);

        }
        if($_GET['ids']){
            if($this->display_restaurant)
                return $this->outputManyRestaurant();
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        return $this->outputManyDiscount($m);
    }

    function outputManyDiscount(){
        $data = $this->model;
        $output = array();

        $output['discounts'] = $this->outputMany($data);

        $offer = $this->add('Model_Offer');
        $output['offers'] = $offer->getRows();
        return $output;

    } 

    function outputManyRestaurant(){
        
        $city = $this->add('Model_City')->addCondition('name',$_GET['city'])->tryLoadAny();
        if(!$city->loaded())
            throw new \Exception("some thing wrong 10006");
            
        $rests = $this->add('Model_Restaurant');
        $rests->addCondition('status','active');
        $rests->addCondition('is_verified',true);

        $rests->setOrder('discount_percentage','desc');
        $rests->setOrder('rating','desc');

        if($_GET['type'] === "discount"){
            $rests->addCondition('discount_id',explode(",",$_GET['ids']));
            $rests->addCondition('city_id',$city->id);
            if($_GET['next']==1)
                $rests->addCondition('id','>',$_GET['last_id']);
            if($_GET['previous'] == 1)
                $rests->addCondition('id','<',$_GET['last_id']);
        }

        if($_GET['type'] === "offer"){
            $rests_join = $rests->join('restaurant_offer.restaurant_id','id');
            $rests_join->addField('offer_id');
            $rests->addCondition('offer_id',explode(",", $_GET['ids']));
            $q = $rests->dsql();
            $group_element = $q->expr('[0]',[$rests->getElement('id')]);
            $rests->_dsql()->group($group_element);
        }

        if($_GET['paginator'] === "next"){
            $rests->addCondition('id','>',$_GET['offset']);

        }elseif($_GET['paginator'] === "previous"){
            $rests->addCondition('id','<',$_GET['offset']);
            $rests->setOrder('id','desc');
        }else{
            $offset = 0;
        }

        
        $this->totalRecord = $rests->count()->getOne();
        $rests->setLimit($_GET['limit']?:10);

        if(!$rests->count()->getOne()){
            echo "no record found";
            exit;
        }
        
        $output = [];
        $rest_recommend = [];
        $last_id = 0;
        $first_id = 0;
        $count = 1;
        $rest_data = [];
        
        foreach ($rests as $rest) {
            if($count==1)
                $first_id = $rest['id'];

            $rest_data['id'] = $rest['id'];
            $rest_data['name'] = $rest['name'];
            $rest_data['longitude'] = $rest['longitude'];
            $rest_data['latitude'] = $rest['latitude'];
            $rest_data['logo_image'] = $rest['logo_image'];
            $rest_data['display_image'] = $rest['display_image'];
            $rest_data['address'] = $rest['address'];
            $rest_data['avg_cost_per_person_veg'] = $rest['avg_cost_per_person_veg'];
            $rest_data['avg_cost_per_person_nonveg'] = $rest['avg_cost_per_person_nonveg'];
            $rest_data['avg_cost_per_person_thali'] = $rest['avg_cost_per_person_thali'];
            $rest_data['avg_cost_of_a_beer'] = $rest['avg_cost_of_a_beer'];
            $rest_data['food_type'] = $rest['food_type'];
            $rest_data['offer_count'] = $rest['offers'];
            $rest_data['rating'] = $rest['rating'];
            $rest_data['discount'] = $rest['discount'];
            $rest_data['discount_id'] = $rest['discount_id'];

            // getting offers
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
         
        $original_last_id = $rests->setOrder('id','desc')->setLimit(1)->tryLoadAny()->get('id');
                
                // //get last restaurant id
        $next_url = null;
        $previous_url = null;

        if($_GET['paginator'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"next",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"previous",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
        }
        elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"next",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"previous",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
        }else{
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"next",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$last_id,'paginator'=>"next",'city'=>$_GET['city'],'required'=>$_GET['required'],'ids'=>$_GET['ids'],'type'=>$_GET['type']]);
        }

        $data_out['next_url'] = $next_url;
        $data_out['previous_url'] = $previous_url;
        // $data_out['next_url'] = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'last_id'=>$last_id,"next"=>true,'city'=>$_GET['city'],"type"=>$_GET['type'],'id'=>$_GET['id'],'required'=>$_GET['required']]);
        
        // $data_out['previous_url'] = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'last_id'=>$last_id,"previous"=>true,'city'=>$_GET['city'],"type"=>$_GET['type'],'id'=>$_GET['id'],'required'=>$_GET['required']]);
        
        return $data_out;
    }

    function _model(){
        if($_GET['type']==="offer")
            $this->model_class = "Offer";

        $this->validateParams();
        $model = parent::_model();
        
        // $model->setOrder('is_recommend','desc');
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
        //todo check for the unwanted url
        if($_GET['type'] and is_string($_GET['type'])){

            if( $_GET['type'] and !in_array($_GET['type'], array('discount','offer')))
                throw new \Exception("some thing wrong 1001");

            if(!$_GET['required'] and $_GET['required'] !="restaurant" )
                throw new \Exception("some thing error 1002", 1);

            if(!$_GET['ids'])
                throw new \Exception("some thing error 1004, pass 'ids' instead of 'id'", 1);

            if(!$_GET['city'] and !is_string($_GET['city']))
                throw new \Exception("some thing error 1005", 1);
                            
            if(!$_GET['limit'] or $_GET['limit'] > $this->app->getConfig('max_limit'))
                throw new \Exception("some thing error 1006", 1);

            $this->display_restaurant = true;
        }

    }
}