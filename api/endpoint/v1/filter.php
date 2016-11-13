<?php

class endpoint_v1_filter extends HungryREST {
    public $model_class = 'Restaurant';
    public $allow_list=true;
    public $allow_list_one=false;
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

        $m = $this->model;
        
        if(!$m)throw $this->exception('Specify model class or define your method handlers');

        if($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            return $this->outputOne($o);
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        return $this->outputManyRestaurant($m);
    }

    function outputManyRestaurant(){

        $data = $this->model->getRows();
        $last_id = 0;
        $first_id = 0;
        $count = 1;
        $output = [];
        foreach ($data as $rest) {
            // $price_array = [];
            // if($rest['avg_cost_per_person_nonveg'] and $rest['avg_cost_per_person_nonveg'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_nonveg'];
            
            // if($rest['avg_cost_per_person_veg'] and $rest['avg_cost_per_person_veg'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_veg'];
            
            // if($rest['avg_cost_per_person_thali'] and $rest['avg_cost_per_person_thali'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_thali'];
            
            // if($rest['avg_cost_of_a_beer'] and $rest['avg_cost_of_a_beer'] > 0)
            //     $price_array[] = $rest['avg_cost_of_a_beer'];

            // $low_price = 10000000;
            // if(count($price_array))
            //     $low_price = min($price_array);
            // echo "id = ".$rest['id']." , name = ".$rest['name']." , ".$rest['avg_cost_per_person_nonveg']." , ".$rest['avg_cost_per_person_veg']." , ".$rest['avg_cost_per_person_thali']." , ".$rest['avg_cost_of_a_beer']." , ".$rest['api_low_price']."<br/>";
            if($count==1)
                $first_id = $rest['id'];

            if($_GET['min_price']){
                if(! ($rest['api_low_price'] >= $_GET['min_price']))
                    continue;
            }
            
            if($_GET['max_price']){
                if( !($_GET['max_price'] >= $rest['api_low_price']))
                    continue;
            }             
            
             //get offers
            $offer_asso = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$rest['id'])
                            ->addCondition('is_active',true)
                            ;

            $offers_temp = [];
            foreach ($offer_asso as $temp) {
                $offers_temp[] = ['id'=>$temp['id'],'name'=>$temp['name'],'detail'=>$temp['detail']];
            }
            
            $output[] = [   
                            'id'=>$rest['id'],
                            'name'=>$rest['name'],
                            'address'=>$rest['address'],
                            'longitude'=>$rest['longitude'],
                            'latitude'=>$rest['latitude'],
                            'offer_count'=>$rest['offers'],
                            'discount_id'=>$rest['discount_id'],
                            'discount'=>($rest['discount_percentage'] - $rest['discount_subtract']?:0),
                            'rating'=>$rest['rating']?((float)$rest['rating']):0,
                            'avg_cost_of_a_beer'=>$rest['avg_cost_of_a_beer'],
                            'avg_cost_per_person_veg'=>$rest['avg_cost_per_person_veg'],
                            'avg_cost_per_person_nonveg'=>$rest['avg_cost_per_person_nonveg'],
                            'avg_cost_per_person_thali'=>$rest['avg_cost_per_person_thali'],
                            'min_price'=>$rest['api_low_price'],
                            'food_type'=>$rest['food_type'],
                            'logo_image' => $rest['logo_image'],
                            'banner_image'=>$rest['banner_image'],
                            'display_image'=>$rest['display_image'],
                            'restaurant_offers' => $offers_temp
                        ];

            $last_id = $rest['id'];
            $count++;    
        }

        if(count($output)) {
            $output = array('restaurants'=>$output);

            $next_url = null;
            $previous_url = null;

            if($_GET['type'] === "next"){
                if($this->totalRecord > $_GET['limit'])
                    $next_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                            'city'=>$_GET['city'],
                                                                                            'lat'=>$_GET['lat'],
                                                                                            'lng'=>$_GET['lng'],
                                                                                            'min_price'=>$_GET['min_price'],
                                                                                            'max_price'=>$_GET['max_price'],
                                                                                            'order'=>$_GET['order'],
                                                                                            'discount'=>$_GET['discount'],
                                                                                            'table_reservation'=>$_GET['table_reservation'],
                                                                                            'highlight'=>$_GET['highlight'],
                                                                                            'popularity'=>$_GET['popularity'],
                                                                                            'rating_order'=>$_GET['rating_order'],
                                                                                            'rating'=>$_GET['rating'],
                                                                                            'cat_id'=>$_GET['cat_id'],
                                                                                            'offset'=>$last_id,
                                                                                            'type'=>"next",
                                                                                            'limit'=>$_GET['limit']
                                                                                            ]);


                if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                    $previous_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                            'city'=>$_GET['city'],
                                                                                            'lat'=>$_GET['lat'],
                                                                                            'lng'=>$_GET['lng'],
                                                                                            'min_price'=>$_GET['min_price'],
                                                                                            'max_price'=>$_GET['max_price'],
                                                                                            'order'=>$_GET['order'],
                                                                                            'discount'=>$_GET['discount'],
                                                                                            'table_reservation'=>$_GET['table_reservation'],
                                                                                            'highlight'=>$_GET['highlight'],
                                                                                            'popularity'=>$_GET['popularity'],
                                                                                            'rating_order'=>$_GET['rating_order'],
                                                                                            'rating'=>$_GET['rating'],
                                                                                            'cat_id'=>$_GET['cat_id'],
                                                                                            'offset'=>$last_id,
                                                                                            'type'=>"previous",
                                                                                            'limit'=>$_GET['limit']
                                                                                        ]);
            }
            elseif($_GET['type'] === "previous"){
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                            'city'=>$_GET['city'],
                                                                                            'lat'=>$_GET['lat'],
                                                                                            'lng'=>$_GET['lng'],
                                                                                            'min_price'=>$_GET['min_price'],
                                                                                            'max_price'=>$_GET['max_price'],
                                                                                            'order'=>$_GET['order'],
                                                                                            'discount'=>$_GET['discount'],
                                                                                            'table_reservation'=>$_GET['table_reservation'],
                                                                                            'highlight'=>$_GET['highlight'],
                                                                                            'popularity'=>$_GET['popularity'],
                                                                                            'rating_order'=>$_GET['rating_order'],
                                                                                            'rating'=>$_GET['rating'],
                                                                                            'cat_id'=>$_GET['cat_id'],
                                                                                            'offset'=>$last_id,
                                                                                            'type'=>"next",
                                                                                            'limit'=>$_GET['limit']
                                                                        ]);
                if($this->totalRecord > 1)
                    $previous_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                                    'city'=>$_GET['city'],
                                                                                                    'lat'=>$_GET['lat'],
                                                                                                    'lng'=>$_GET['lng'],
                                                                                                    'min_price'=>$_GET['min_price'],
                                                                                                    'max_price'=>$_GET['max_price'],
                                                                                                    'order'=>$_GET['order'],
                                                                                                    'discount'=>$_GET['discount'],
                                                                                                    'table_reservation'=>$_GET['table_reservation'],
                                                                                                    'highlight'=>$_GET['highlight'],
                                                                                                    'popularity'=>$_GET['popularity'],
                                                                                                    'rating_order'=>$_GET['rating_order'],
                                                                                                    'rating'=>$_GET['rating'],
                                                                                                    'cat_id'=>$_GET['cat_id'],
                                                                                                    'limit'=>$_GET['limit'],
                                                                                                    'offset'=>$first_id,
                                                                                                    'type'=>"previous"
                                                                                                    ]);
            }else{
                if($this->totalRecord > $_GET['limit'])
                    $next_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                                    'city'=>$_GET['city'],
                                                                                                    'lat'=>$_GET['lat'],
                                                                                                    'lng'=>$_GET['lng'],
                                                                                                    'min_price'=>$_GET['min_price'],
                                                                                                    'max_price'=>$_GET['max_price'],
                                                                                                    'order'=>$_GET['order'],
                                                                                                    'discount'=>$_GET['discount'],
                                                                                                    'table_reservation'=>$_GET['table_reservation'],
                                                                                                    'highlight'=>$_GET['highlight'],
                                                                                                    'popularity'=>$_GET['popularity'],
                                                                                                    'rating_order'=>$_GET['rating_order'],
                                                                                                    'rating'=>$_GET['rating'],
                                                                                                    'cat_id'=>$_GET['cat_id'],
                                                                                                    'limit'=>$_GET['limit'],
                                                                                                    'offset'=>$last_id,
                                                                                                    'type'=>"next"
                                                                                                ]);
                // echo "<pre>";
                // print_r($_GET['limit']);
                // exit;
                if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                    $previous_url = $this->app->getConfig('apipath').$this->app->url(null,[
                                                                                                    'city'=>$_GET['city'],
                                                                                                    'lat'=>$_GET['lat'],
                                                                                                    'lng'=>$_GET['lng'],
                                                                                                    'min_price'=>$_GET['min_price'],
                                                                                                    'max_price'=>$_GET['max_price'],
                                                                                                    'order'=>$_GET['order'],
                                                                                                    'discount'=>$_GET['discount'],
                                                                                                    'table_reservation'=>$_GET['table_reservation'],
                                                                                                    'highlight'=>$_GET['highlight'],
                                                                                                    'popularity'=>$_GET['popularity'],
                                                                                                    'rating_order'=>$_GET['rating_order'],
                                                                                                    'rating'=>$_GET['rating'],
                                                                                                    'cat_id'=>$_GET['cat_id'],                                
                                                                                                    'limit'=>$_GET['limit'],
                                                                                                    'offset'=>$last_id,
                                                                                                    'type'=>"previous"
                                                                                            ]);
            }

            $output['next_url'] = $next_url;
            $output['previous_url'] = $previous_url;
            return $output;
        }
        else
            return "no record found";
    }

    function _model(){

        $this->validateParams();
        $model = parent::_model();

        $model->addCondition('status','active');
        $model->addCondition('is_verified',true);

        $model->addExpression('city')->set(function($m,$q){
            return $q->expr('UPPER([0])',[$m->refSQL('city_id')->fieldQuery('name')]);
        });
        $model->addCondition('city',strtoupper($_GET['city']));
        
        
        if($_GET['popularity'])
            $model->addCondition('is_popular',true);

        
        //restaurant has discount or offers awesome
        if($_GET['discount']){
            $q = $model->dsql();
            $offers_count = $q->expr('[0]',[$model->refSQL('RestaurantOffer')->addCondition('is_active',true)->count()]);
            $model->addCondition(
                        $q->orExpr()
                            ->where('discount_id',"<>",null)
                            ->where($offers_count,'>',0)
                    );
        }

        //table reservation
        if($_GET['table_reservation']){
            $model->addCondition('reservation_needed',true);
        }

    
        //near by restaurant
        if($_GET['lat'] and $_GET['lng']){
            $current_lat = $_GET['lat'];
            $current_long = $_GET['lng'];
            $q = $model->dsql();

            $latlng = $q->expr('ABS(ABS([0] - [1]) + ABS([2] - [3]))',[$model->getElement('latitude'),$current_lat,$model->getElement('longitude'),$current_long]);
            $model->addCondition($latlng,"<=",10);
            $model->setOrder($latlng,'asc');
        }

        
        
        //Highlight Filter Data
        $selected_filter_highlight_array = [];
        if($_GET['highlight'])
            $selected_filter_highlight_array =  explode(",", $_GET['highlight']);

        if(count($selected_filter_highlight_array)){
            $highlight_asso_j = $model->join('restaurant_highlight_association.restaurant_id','id');
            $highlight_asso_j->addField('Highlight_id');
            $highlight_asso_j->addField('rest_id','restaurant_id');

            $highlight_j = $highlight_asso_j->join('Highlight.id','Highlight_id');
            $highlight_j->addField('active_highlight','is_active');

            $model->addCondition('active_highlight',true);
            $q = $model->dsql();

            $model->addCondition('Highlight_id',$selected_filter_highlight_array);
            $group_element = $q->expr('[0]',[$model->getElement('rest_id')]);
            $model->_dsql()->group($group_element);
        }
            
        // apply category wise condition
        if($_GET['cat_id'] and is_numeric($_GET['cat_id'])){
            $cat_asso_j = $model->join('category_restaurant_asso.restaurant_id','id');
            $cat_asso_j->addField('category_id');
            $cat_asso_j->addField('category_assos_rest_id','restaurant_id');

            $model->addCondition('category_id',$_GET['cat_id']);

            $group_element = $model->dsql()->expr('[0]',[$model->getElement('category_assos_rest_id')]);
            $model->_dsql()->group($group_element);
        }


        // try again via expression for calculating min and max price
        // if($_GET['min_price'] and $_GET['max_price']){
        $model->addExpression('api_low_price')->set(function($m,$q){
            return $q->expr("(least( IF( [0] > 0,[0],10000000), IF([1] > 0,[1],10000000), IF([2] > 0,[2],10000000), IF([3] > 0,[3],10000000) ))",[$m->getElement('avg_cost_per_person_nonveg'),$m->getElement('avg_cost_per_person_veg'),$m->getElement('avg_cost_per_person_thali'),$m->getElement('avg_cost_of_a_beer')]);
            // $price_array = [];
            // if($rest['avg_cost_per_person_nonveg'] and $rest['avg_cost_per_person_nonveg'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_nonveg'];
            
            // if($rest['avg_cost_per_person_veg'] and $rest['avg_cost_per_person_veg'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_veg'];
            
            // if($rest['avg_cost_per_person_thali'] and $rest['avg_cost_per_person_thali'] > 0)
            //     $price_array[] = $rest['avg_cost_per_person_thali'];
            
            // if($rest['avg_cost_of_a_beer'] and $rest['avg_cost_of_a_beer'] > 0)
            //     $price_array[] = $rest['avg_cost_of_a_beer'];

            // $low_price = 10000000;
            // if(count($price_array))
            //     $low_price = min($price_array);
        });
        // }


        //Price wise Order
        $order_sequence = "desc";
        if($_GET['order']=="asc"){
            $order_sequence = "asc";
        }
        
        $model->setOrder('api_low_price',$order_sequence);
        // $model->setOrder('avg_cost_per_person_nonveg',$order_sequence);
        // $model->setOrder('avg_cost_per_person_thali',$order_sequence);
        // $model->setOrder('avg_cost_of_a_beer',$order_sequence);
    
    //rating 
        if($_GET['rating']){
            $model->addCondition('rating',">=",$_GET['rating']);
            $model->setOrder('asc','rating');
        }


        $rating_order = "desc";
        if($_GET['rating_order']==="asc")
            $rating_order = "asc";

        $model->setOrder('rating',$rating_order);
    
        if($_GET['type'] === "next"){
            $model->addCondition('id','>',$_GET['offset']);

        }elseif($_GET['type'] === "previous"){
            $model->addCondition('id','<',$_GET['offset']);
            $model->setOrder('id','desc');
        }else{
            $offset = 0;
        }
        
        $this->totalRecord = $model->count()->getOne();
        if($_GET['limit'] ){            
            $model->setLimit($_GET['limit']);
        }

        if($model->count()->getOne() === 0){
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

        $required_param_variable = [
                                        'city',
                                        'lat',
                                        'lng',
                                        'min_price',
                                        'max_price',
                                        'order',
                                        'discount',
                                        'table_booking',
                                        'highlight',
                                        'popularity',
                                        'rating_order',
                                        'limit'
                                ];

        if(count($_GET) > 14)
            throw new \Exception("some thing wrong  10001");

        if( (isset($_GET['min_price']) + isset($_GET['max_price'])) == 1){
            echo "must pass min amd max price";
            exit;
        }

        
        if($_GET['min_price'] > $_GET['max_price']){
            echo "min price cannot be greater than max price";
            exit;
        }

        if(!$_GET['city']){
            echo "must pass city name";
            exit;
        }

        if(!$_GET['limit'] or !is_numeric($_GET['limit'])){
            echo "must pass limit";
            exit;
        }


    }

}


// http://localhost/hungrydunia/api/?page=v1_filter&city=udaipur&highlight=1,2,3,4,5&cat_id=2