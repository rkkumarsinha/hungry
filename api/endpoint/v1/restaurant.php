<?php

class endpoint_v1_restaurant extends HungryREST {
    public $model_class = 'Restaurant';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $single_restaurant = false;
    function init(){
    	parent::init();

    	// throw new \Exception(print_r($_GET));        
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
        if(!$_GET['city'])
            throw new \Exception("some thing wrong... 0001"); //city

        if($_GET['area_id'] and !is_numeric($_GET['area_id']))
            throw new \Exception("some thing wrong...1001"); //area id

        if($_GET['featured'] and !($_GET['featured']==="required"))
            throw new \Exception("some thing wrong...2001"); //required

        // throw new \Exception("Error Processing Request", 1);
        if($_GET['limit'] and !(is_numeric($_GET['limit']))){
            throw new \Exception("some thing wrong...3001");
        }

        if($_GET['last_id'] and !(is_numeric($_GET['last_id'])))
            throw new \Exception("some thing wrong...4001"); //last id must numeric

        if($_GET['first_id'] and !(is_numeric($_GET['first_id'])))
            throw new \Exception("some thing wrong...5001"); //first_id must numeric
                
        //check for the area id
        $m=$this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        // $o = $m->getRows();

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $this->single_restaurant = true;
            return $this->outputRestMany($m->id);
            // return $this->outputRestMany($m->id);
            // return $this->outputOne($m->get());
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        // echo "<pre>";
        // print_r($this->outputRestMany());
        return $this->outputRestMany();
        // $o['purchases']=$this->outputMany($this->model->ref('Purchases'),false);
    }

    private function outputRestMany($rest_id=0){
        $data = $this->model;
        if($rest_id)
            $data = $this->model->addCondition('id',$rest_id)->tryLoadAny();

        $limit = 10;
        if($_GET['limit'] and is_numeric($_GET['limit']))
            $limit = $_GET['limit'];

        $last_id=0;
        $first_id=0;
        $output = array();
        $count = 1;

        foreach ($data as $row) {
            
            $output[$row['id']] = $this->outputOneRest($row);
            if($count==1)
                $first_id = $row['id'];

            //get offers
            $offer_asso = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$row->id)
                            ->addCondition('is_active',true)
                            ;

            if($this->single_restaurant){
                $offers_temp = [];
                foreach ($offer_asso as $temp) {
                    $offers_temp[] = ['id'=>$temp['id'],'name'=>$temp['name'],'detail'=>$temp['detail']];
                }
                $output[$row['id']]['restaurant_offers'] = $offers_temp;
            }

            $output[$row['id']]['offer_count'] = $offer_asso->count()->getOne();

            // //rest offers 
            // $offer_asso = $this->add('Model_RestaurantOffer')
            //                 ->addCondition('restaurant_id',$rest['id'])
            //                 ->addCondition('is_active',true)
            //                 ;

            // $offers_temp = [];
            // foreach ($offer_asso as $temp) {
            //     $offers_temp[] = ['name'=>$temp['name'],'detail'=>$temp['detail']];
            // }
            // $output['restaurant_offers'] = $offers_temp;

            //get all categories
            if($this->single_restaurant){

                $casso = $this->add('Model_CategoryAssociation')
                            ->addCondition('restaurant_id',$row->id)
                            ->getRows(array('category','icon_url'));
                $output[$row['id']]['categories'] = $this->outputMany($casso);                

                //get all restaurant_images
                $images = $this->add('Model_RestaurantImage')
                        ->addCondition('restaurant_id',$row->id)
                        ->addCondition('status','approved')
                        ->addCondition('is_active',true)
                        ->getRows(array('image'))
                        ;
                $output[$row['id']]['restaurant_images'] = $this->outputMany($images);
                
                //get all menus
                $menus = $this->add('Model_RestaurantMenu')
                        ->addCondition('restaurant_id',$row->id)
                        ->addCondition('status','approved')
                        ->addCondition('is_active',true)
                        ->getRows(array('image'))
                        ;
                $output[$row['id']]['restaurant_menu'] = $this->outputMany($menus);

                //get highlight
                $highlights = $this->add('Model_Restaurant_Highlight')
                        ->addCondition('restaurant_id',$row->id)
                        ->getRows(array('highlight','Highlight','icon_url'))
                        ;
                $output[$row['id']]['highlight'] = $this->outputMany($highlights);

                //get keyword
                $keywords = $this->add('Model_Restaurant_Keyword')
                        ->addCondition('restaurant_id',$row->id)
                        ->getRows(array('keyword','Keyword','icon_url'))
                        ;
                $output[$row['id']]['keyword'] = $this->outputMany($keywords);
                
                //get reviews
                $reviews = $this->add('Model_Review')
                        ->addCondition('restaurant_id',$row->id)
                        ->addCondition('is_approved',true)
                        ->getRows()
                        ;
                $temp_rew = [];
                foreach ($reviews as $rew) {
                    $comment_model =$this->add('Model_Comment')->addCondition('review_id',$rew['id'])->addCondition('is_approved',true);
                    $temp_rew[] = [
                                    'id'=>$rew['id'],
                                    "restaurant_id"=>$rew['restaurant_id'],
                                    "restaurant"=>$rew['restaurant'],
                                    "user_id"=>$rew['user_id'],
                                    "user"=>$rew['user'],
                                    "user_profile"=>$rew['user_profile'],
                                    "title"=>$rew['title'],
                                    "review"=>$rew['comment'],
                                    "created_at"=>$rew['created_at'],
                                    "created_time"=>$rew['created_time'],
                                    "comment"=>$this->outputMany($comment_model),
                                    'rating'=>$rew['rating']
                                    ];
                }

                $output[$row['id']]['review'] = $temp_rew;

                //get events
                $events_asso = $this->add('Model_EventDestinationRest')
                        ->addCondition('restaurant_id',$row->id)
                        ->getRows()
                        ;
                $temp_event = [];
                foreach ($events_asso as $temp) {
                    $event_model = $this->add('Model_Event')
                                        ->addCondition('id',$temp['event_id'])
                                        ->addCondition('closing_date','>',$this->api->today)
                                        ->tryLoadany();
                    
                    if(!$event_model->loaded())
                        continue;
                        
                    $temp_event[] = [
                                    'id'=>$event_model['id'],
                                    'name'=>$event_model['name'],
                                    'detail'=>$event_model['detail'],
                                    'starting_date'=>$event_model['starting_date'],
                                    'closing_date'=>$event_model['closing_date'],
                                    'display_image'=>$event_model['display_image'],
                                    'lowest_price'=>$event_model['lowest_price'],
                                    'total_day'=>$event_model['total_day'],
                                    'closing_time'=>$event_model['closing_time'],
                                    'starting_time'=>$event_model['starting_time'],
                                    'disclaimer'=>$event_model['disclaimer']
                                ];
                }
                $output[$row['id']]['events'] = $temp_event;
            }

            $last_id = $row['id'];
            $count++;
        }

        // throw new \Exception($this->app->getConfig('frontendpath'));
        // echo "<pre>";
        return $data = array(
                        'restaurants'=>array_values($output),
                        'next_url'=>$this->app->getConfig('apipath').$this->app->url(null,['limit'=>$limit,'last_id'=>$last_id,'type'=>"next",'first_id'=>$first_id,'city'=>$_GET['city'],'area_id'=>$_GET['area_id']]),
                        'previous_url'=>$this->app->getConfig('apipath').$this->app->url(null,['limit'=>$limit,'last_id'=>$last_id,'first_id'=>$first_id,'type'=>"previous",'city'=>$_GET['city'],'area_id'=>$_GET['area_id']])
                    );
        // print_r($data);
    }

    function _model(){
        $limit = 10;
        $last_id = 0;
        $first_id = 0;

        if($_GET['limit'] and is_numeric($_GET['limit']))
                $limit = $_GET['limit'];
        
        if($_GET['last_id'] and is_numeric($_GET['last_id']))
            $last_id = $_GET['last_id'];

        if($_GET['first_id'] and is_numeric($_GET['first_id']))
            $first_id = $_GET['first_id'];


        $city = $this->add('Model_City')->addCondition('name',$_GET['city'])->tryLoadAny();

        if($_GET['area_id'] and is_numeric($_GET['area_id'])){
            
            if($_GET['featured'] and $_GET['featured']==="required"){
                if($_GET['type']=="next")
                    return parent::_model()
                            ->addCondition('city_id',$city->id)
                            ->addCondition('area_id',$_GET['area_id'])
                            ->addCondition('id','>',$last_id)
                            ->addCondition('is_featured',true)
                            ->addCondition('is_verified',true)
                            ->addCondition('status','active')
                            ->setLimit($limit);

                elseif($_GET['type']=="previous")
                    return parent::_model()
                                    ->addCondition('city_id',$city->id)
                                    ->addCondition('area_id',$_GET['area_id'])
                                    ->addCondition('is_featured',true)
                                    ->addCondition('id','<',$last_id)
                                    ->addCondition('is_verified',true)
                                    ->addCondition('status','active')
                                    ->setOrder('id','desc')
                                    ->setLimit($limit);
                else
                    return parent::_model()
                                    ->addCondition('city_id',$city->id)
                                    ->addCondition('area_id',$_GET['area_id'])
                                    ->addCondition('is_featured',true)
                                    ->addCondition('is_verified',true)
                                    ->addCondition('status','active')
                                    ->setLimit($limit);
            }

            if($_GET['type'] == "next")
                return parent::_model()
                                ->addCondition('city_id',$city->id)
                                ->addCondition('area_id',$_GET['area_id'])
                                ->addCondition('id','>',$last_id)
                                ->addCondition('is_verified',true)
                                ->addCondition('status','active')
                                ->setLimit($limit);
            elseif($_GET['type']=="previous")
                return parent::_model()
                                ->addCondition('city_id',$city->id)
                                ->addCondition('area_id',$_GET['area_id'])
                                ->addCondition('id','<',$last_id)
                                ->addCondition('is_verified',true)
                                ->addCondition('status','active')
                                ->setOrder('id','desc')
                                ->setLimit($limit);
            else
                return parent::_model()->addCondition('city_id',$city->id)
                                ->addCondition('area_id',$_GET['area_id'])
                                ->addCondition('is_verified',true)
                                ->addCondition('status','active')
                                ->setLimit($limit);
        }

        if($_GET['featured'] and $_GET['featured']==="required"){
            if($_GET['type']=="next") 
                return parent::_model()
                            ->addCondition('city_id',$city->id)
                            ->addCondition('is_featured',true)
                            ->addCondition('id','>',$last_id)
                            ->addCondition('is_verified',true)
                            ->addCondition('status','active')
                            ->setLimit($limit);
            elseif($_GET['type'] =="previous")
                return parent::_model()
                            ->addCondition('city_id',$city->id)
                            ->addCondition('is_featured',true)
                            ->addCondition('id','<',$last_id)
                            ->addCondition('is_verified',true)
                            ->addCondition('status','active')
                            ->setOrder('id','desc')
                            ->setLimit($limit);
            else
                return parent::_model()
                            ->addCondition('city_id',$city->id)
                            ->addCondition('is_featured',true)
                            ->addCondition('is_verified',true)
                            ->addCondition('status','active')
                            ->setLimit($limit);
        }
        

        if($_GET['type']=="next")
            return parent::_model()
                    ->addCondition('city_id',$city->id)
                    ->addCondition('id','>',$last_id)
                    ->addCondition('is_verified',true)
                    ->addCondition('status','active')
                    ->setLimit($limit);

        elseif($_GET['type'] =="previous")
             return parent::_model()
                    ->addCondition('city_id',$city->id)
                    ->addCondition('id','<',$last_id)
                    ->addCondition('is_verified',true)
                    ->addCondition('status','active')
                    ->setOrder('id','desc')
                    ->setLimit($limit);
        else{            
            return parent::_model()
                    ->addCondition('city_id',$city->id)
                    ->addCondition('is_verified',true)
                    ->addCondition('status','active')
                    ->setLimit($limit);
        }

    }

	function put($data){
        // return json_encode($data);
        return $this->model->id;
	}

	function delete($data){

	}

    function outputOneRest($model){
        $temp1 = [];
        if($this->single_restaurant){
            $temp1 = array(
                    "monday"=>$model['monday'],
                    "tuesday"=>$model['tuesday'],
                    "wednesday"=>$model['wednesday'],
                    "thursday"=>$model['thursday'],
                    "friday"=>$model['friday'],
                    "saturday"=>$model['saturday'],
                    "sunday"=>$model['sunday'],
                    "banner_image"=>$model['banner_image'],
                    "owner_name"=>$model['owner_name'],
                    "about_restaurant"=>$model['about_restaurant'],
                    "mobile_no"=>$model['mobile_no'],
                    "phone_no"=>$model['phone_no'],
                    "email"=>$model['email'],
                    "website"=>$model['website'],
                    "facebook_page_url"=>$model['facebook_page_url'],
                    "instagram_page_url"=>$model['instagram_page_url'],
                    "created_at"=>$model['created_at'],
                    "updated_at"=>$model['updated_at']
                );
        }

        $temp2 = array(
                    'id'=>$model->id,
                    'country_id'=>$model['country_id'],
                    'country'=>$model['country'],
                    'city_id'=>$model['city_id'],
                    'city'=>$model['city'],
                    'state_id'=>$model['state_id'],
                    'state'=>$model['state'],
                    'area_id'=>$model['area_id'],
                    'area'=>$model['area'],
                    'discount_id'=>$model['discount_id'],
                    'discount'=>$model['discount_percentage_to_be_given'],
                    "logo_image"=>$model['logo_image'],
                    "display_image"=>$model['display_image'],
                    "name"=>$model['name'],
                    "address"=>$model['address'],
                    "rating"=>$model['rating'],
                    "avg_cost_per_person_veg"=>$model['avg_cost_per_person_veg'],
                    "avg_cost_per_person_nonveg"=>$model['avg_cost_per_person_nonveg'],
                    "avg_cost_per_person_thali"=>$model['avg_cost_per_person_thali'],
                    "avg_cost_of_a_beer"=>$model['avg_cost_of_a_beer'],
                    "credit_card_accepted"=>$model['credit_card_accepted'],
                    "reservation_needed"=>$model['reservation_needed'],
                    "type"=>$model['type'],
                    "longitude"=>$model['longitude'],
                    "latitude"=>$model['latitude'],
                    "is_featured"=>$model['is_featured'],
                    "is_popular"=>$model['is_popular'],
                    "is_recommend"=>$model['is_recommend'],
                    "url_slug"=>$model['url_slug'],
                    "food_type"=>$model['food_type'],
                    "discount_percentage"=>$model['discount_percentage_to_be_given'],
                    "category_icon_url"=>$model['category_icon_url']
                );
        return array_merge($temp2,$temp1);
    }
}