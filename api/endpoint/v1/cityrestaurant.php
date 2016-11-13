<?php

class endpoint_v1_cityrestaurant extends HungryREST {
    public $model_class = 'City';
    public $allow_list=true;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

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
        if($_GET['city'] and !is_string($_GET['city']))
            throw new \Exception("some thing wrong");

        //check for the area id
        $m=$this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            $o = $m->get();
            $o['images'] = $this->outputManyImage();
            $o['area'] = $this->outputMany($m->ref('Area')->getRows(array('name','latitude','longitude','state')));
            $o['restaurants'] = array_values($this->outputManyFeaturedRestaurant($m->id));
            // echo "<pre>";
            // print_r($this->outputOne($o));
            return $this->outputOne($o);
        }

        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');

        // $o['images'] = $this->outputMany($m->refSQL('Image')->addCondition('is_active',true)->getRows(array('image')));
        return $this->outputManyCity();
    }
    function outputManyImage($image_model = null){


        if($image_model){
            $images = $image_model;            
        }else{
            $images = $this->model->ref('Image')->addCondition('is_active',true)->getRows();
        }

               
        $temp = [];
        foreach ($images as $img) {
            $temp[] = [
                    "image"=>$img['image'],
                    "redirect_url"=>$img['redirect_url']?:null,
                    "restaurant_id"=>$img['app_restaurant_id']?:null,
                    "destination_id"=>$img['app_destination_id']?:null,
                    "event_id"=>$img['app_event_id']?:null,
                    'city_id'=>$img['city_id']?:null,
                    "area_id"=>$img['area_id']?:null
                ];
        }

        return $temp;
    }

    function outputManyCity(){
        $data = $this->model;
        $output = array();
        foreach ($data as $row) {
            $output[$row['id']] = $this->outputOne($row);

            $city_image = $this->add('Model_Image')
                    ->addCondition('city_id',$row->id)
                    ->addCondition('is_active',true)
                    ->getRows(array('name','redirect_url','is_active','image_id','image'));
            $output[$row['id']]['images'] = $this->outputManyImage($city_image);
            $output[$row['id']]['area'] = $this->outputMany($this->model->ref('Area')->getRows(array('name','latitude','longitude','state')));
            $output[$row['id']]['restaurant'] = array_values($this->outputManyFeaturedRestaurant($row->id));
        }
        return array_values($output);

    }

    function outputManyFeaturedRestaurant($city_id){        
        $restaurant = $this->add('Model_Restaurant')
            ->addCondition('city_id',$city_id)
            ->addCondition('is_featured',true)
            ->addCondition('status','active')
            ->addCondition('is_verified',true)
            ;

        $data = [];
        foreach ($restaurant as $rest) {
            $data[$rest->id] = array(
                            'id'=>$rest->id,
                            'country_id'=>$rest['country_id'],
                            'country'=>$rest['country'],
                            'city_id'=>$rest['city_id'],
                            'city'=>$rest['city'],
                            'state_id'=>$rest['state_id'],
                            'state'=>$rest['state'],
                            'area_id'=>$rest['area_id'],
                            'area'=>$rest['area'],
                            'discount'=>$rest['discount'],
                            "logo_image"=>$rest['logo_image'],
                            "display_image"=>$rest['display_image'],
                            "name"=>$rest['name'],
                            "address"=>$rest['address'],
                            "rating"=>$rest['rating'],
                            "avg_cost_per_person_veg"=>$rest['avg_cost_per_person_veg'],
                            "avg_cost_per_person_nonveg"=>$rest['avg_cost_per_person_nonveg'],
                            "avg_cost_per_person_thali"=>$rest['avg_cost_per_person_thali'],
                            "avg_cost_of_a_beer"=>$rest['avg_cost_of_a_beer'],
                            "credit_card_accepted"=>$rest['credit_card_accepted'],
                            "reservation_needed"=>$rest['reservation_needed'],
                            "type"=>$rest['type'],
                            "longitude"=>$rest['longitude'],
                            "latitude"=>$rest['latitude'],
                            "is_featured"=>$rest['is_featured'],
                            "is_popular"=>$rest['is_popular'],
                            "is_recommend"=>$rest['is_recommend'],
                            "food_type"=>$rest['food_type'],
                            "discount_percentage"=>$rest['discount_percentage'],
                            "category_icon_url"=>$rest['category_icon_url']
                        );
            //get offers
            $offer_asso = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$rest->id)
                            ->addCondition('is_active',true)
                            ;
            $data[$rest->id]['offer_count'] = $offer_asso->count()->getOne();
        }
        return $data;
    }

    function _model(){
        if($_GET['city']){   
            return parent::_model()->addCondition('name',$_GET['city'])->tryLoadAny();
        }
        return parent::_model();  
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

}