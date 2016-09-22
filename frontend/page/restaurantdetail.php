<?php

class page_restaurantdetail extends Page{
	
    public $gallery_model;
	public $menu_model;
	public $restaurant_model;
	public $restaurant_id=0;

    function init(){
        parent::init();

        
        // $path = "http://localhost/hungrydunia/".str_replace("..", "", $f->getPath());
        // $this->api->url()->absolute()->getBaseURL()
        
    	//loading required model
        $slug = trim($this->api->stickyGET('slug'));
    	$restaurant_model = $this->add('Model_Restaurant')->addCondition('url_slug',$slug);
        $restaurant_model->tryLoadAny();

        if(!$restaurant_model->loaded()){
            throw new \Exception("Page Not Found");
            exit;
        }

        $this->restaurant_id = $id = $restaurant_model->id;
                      
    	$this->gallery_model = $this->add('Model_RestaurantImage')->addCondition('restaurant_id',$id);
        $this->setModel($restaurant_model);

        $this->add('View_Restaurant_GetDiscount',['restaurant_id'=>$restaurant_model->id],'getdiscount');
        if($restaurant_model['reservation_needed'])
            $this->add('View_Restaurant_ReserveTable',['restaurant_id'=>$restaurant_model->id],'reservetable');
        
        // $getdiscount_btn = $this->add('Button','null','getdiscount')->set('Get Discount')->addClass('atk-swatch-orange btn-block')->setStyle('border','0px solid white');
        // if($getdiscount_btn->isClicked()){
        //     // 'autoOpen'=>false,'show'=>array('effect'=>'blind','duration'=> 1000)
        //     $options = array('width'=>'800');
        //     $this->js()->univ()->frameURL('Get Your Discount Coupon Here',$this->api->url('getdiscount',array('restaurant_id'=>$id,'cut_page'=>0)),$options)->addClass('hungry-frameurl')->execute();
        // }

        // $reserve_table_btn  = $this->add('Button','null','reservetable')->set('Reserve a Table')->addClass('atk-swatch-green btn-block')->setStyle('border','0px solid white');
        // if($reserve_table_btn->isClicked()){
        //     // 'autoOpen'=>false,'show'=>array('effect'=>'blind','duration'=> 1000)
        //     $options = array('width'=>'800');
        //     $this->js()->univ()->frameURL(
        //                                 'Reserve A Table',
        //                                 $this->api->url(
        //                                             'reservetable',
        //                                             array(
        //                                                 'restaurant_id'=>$id,
        //                                                 'cut_page'=>0
        //                                                 )
        //                                             ),
        //                                             $options
        //                                 )->addClass('hungry-frameurl')->execute();
        // }
        //Add Route Map        
        $view_route_map = $this->add('View_RouteMap',['restaurant_lat'=>$restaurant_model['latitude'],'restaurant_lng'=>$restaurant_model['longitude']],'routemap');
        
        $this->add('View_Review',['restaurant_id'=>$this->restaurant_id,'restaurant_rating'=>$restaurant_model['rating']],'hungryuserrating');
    }

    function setModel($m){

        parent::setModel($m);
        // throw new \Exception($this->model['banner_image']);
        // $banner_image_url = $this->model['banner_image'];
        $banner_image_url =  str_replace("public/", "", $this->model['banner_image']);
        $logo_image_url = str_replace("public/", "", $this->model['logo_image']);
        $this->template->set('rest_banner_image',$banner_image_url);
        $this->template->set('rest_logo_image',$logo_image_url);
    }

    function recursiveRender(){
        
        $gallery = $this->add('View_Lister_RestaurantGallery',['restaurant_id'=>$this->restaurant_id],'gallery');
    	$gallery->setModel($this->gallery_model);

        $this->add('View_Lister_RestaurantMenu',['restaurant_id'=>$this->restaurant_id],'menu');
        
        $this->add('Lister',null,'highlight',['page/restaurantdetail','siglehighlight'])->setModel($this->model->ref('Restaurant_Highlight')->addCondition('is_active',true));
        $this->add('Lister',null,'feature',['page/restaurantdetail','feature'])->setModel($this->model->ref('Restaurant_Highlight')->addCondition('is_active',true));
        $this->add('Lister',null,'cuisine',['page/restaurantdetail','cuisine'])->setModel($this->model->ref('Restaurant_Keyword'));

        //Calculating near by restaurant
        $near_by_restaurant = $this->add('Model_Restaurant');
        $near_by_restaurant->addCondition('city_id',$this->model['city_id']);
        // $near_by_restaurant->addCondition('area_id',$this->model['area_id']);
        // SELECT * FROM `WAYPOINTS` W ORDER BY
        // ABS(ABS(W.`LATITUDE`-53.63) +
        // ABS(W.`LONGITUDE`-9.9)) ASC LIMIT 30;
        $current_lat = $this->model['latitude'];
        $current_long = $this->model['longitude'];

        $near_by_restaurant->addExpression('latlng')->set(function($m,$q)use($current_lat,$current_long){
            return $q->expr('ABS(ABS([0] - [1]) + ABS([2] - [3]))',[$m->getField('latitude'),$current_lat,$m->getField('longitude'),$current_long]);
        });
        $near_by_restaurant->addCondition('id','<>',$this->model->id);
        $near_by_restaurant->setOrder('latlng','asc');
        // $near_by_restaurant->addCondition('latlng','>',($this->model['latitude']));
        $near_by_restaurant->addCondition('status','active');
        $near_by_restaurant->setLimit(3);
        $list = $this->add('View_Lister_NearByRestaurant',null,'nearbyrestaurant');
        $list->setModel($near_by_restaurant);
        //end of near by restaurat

        $recom_restaurant = $this->add('Model_Restaurant')
            ->addCondition('is_recommend',true)
            ->addCondition('city_id',$this->model['city_id'])
            ->setLimit(3);

        $recom = $this->add('View_Lister_NearByRestaurant',null,'recommendedrestaurant');
        $recom->setModel($recom_restaurant);

        $offer_model = $this->add('Model_RestaurantOffer')->addCondition('restaurant_id',$this->restaurant_id);
        $offer = $this->add('Lister',null,'restaurantoffer',['page/restaurantdetail','restaurantoffer']);
        $offer->setModel($offer_model);

        $comment_lister = $this->add('View_Lister_Comment',['restaurant_id'=>$this->restaurant_id],'review');
        $comment = $this->add('Model_Review')
                        ->addCondition('restaurant_id',$this->restaurant_id)
                        ->addCondition('is_approved',true)
                        ->setOrder('id','Desc');
        $comment_lister->setModel($comment);

        if(!$this->api->auth->model->id){
            $this->app->memorize('next_url',$this->app->url());
            $this->add('View_Login',null,'login');
        }

        //checking all if value has or not
        if(!$this->model['mobile_no'])
            $this->template->tryDel('mobile_wrapper');

        if(!$this->model['phone_no'])
            $this->template->tryDel('phone_wrapper');

        if(!$this->model['email'])
            $this->template->tryDel('email_wrapper');
        
        if(!$this->model['website'])
            $this->template->tryDel('website_wrapper');
        
        if(!$this->model['facebook_page_url'])
            $this->template->tryDel('facebook_wrapper');
        
        if(!$this->model['instagram_page_url'])
            $this->template->tryDel('instagram_wrapper');

        if(!$this->model['avg_cost_per_person_veg'] or $this->model['avg_cost_per_person_veg'] == 0)
            $this->template->tryDel('veg_wrapper');
        
        if(!$this->model['avg_cost_per_person_nonveg'] or $this->model['avg_cost_per_person_veg'] == 0)
            $this->template->tryDel('non_veg_wrapper');

        if(!$this->model['avg_cost_per_person_thali'] or $this->model['avg_cost_per_person_veg'] == 0)
            $this->template->tryDel('thali_wrapper');

    	parent::recursiveRender();
    }

    function defaultTemplate(){
    	return ['page/restaurantdetail'];
    }
}