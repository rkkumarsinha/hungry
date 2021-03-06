<?php
class page_index extends Page
{
    function init(){
        parent::init();

        $v = $this->add('View_Lister_HomeSlider',['city'=>$this->app->city_name?:"Udaipur",'type'=>"RestaurantGallery"],'homeslider');
        $this->add('View_Search',null,'search_form');

        // echo $this->app->page."";
        $this->template->trySet('absolute_url',$this->app->getConfig('absolute_url'));
        $this->template->trySet('current_city',$this->app->city_name);
    }

    function recursiveRender(){

        //Featured Restaurant
        $featured_rest_model = $this->add('Model_FeaturedRestaurant');
        $featured_rest_model->addCondition('city_id',$this->app->city_id);
        $featured_rest_model->addCondition('status','active');
        $featured_rest_model->addCondition('is_verified',true);

        $featured_rest_model->setOrder('id','desc')->setLimit(4);
        $list = $this->add('View_Lister_Restaurant',null,'restaurantlist');
        $list->setModel($featured_rest_model);

        //Popular This Week Restaurant
        $popular_model = $this->add('Model_PopularRestaurant');
        $popular_model->addCondition('city_id',$this->app->city_id);
        $popular_model->setOrder('id','desc')->setLimit(4);
        $popular_model->addCondition('status','active');
        $popular_model->addCondition('is_verified',true);
        

        $p_list = $this->add('View_Lister_Restaurant',['template'=>'view/popularrestaurant'],'popularrestaurant');
        $p_list->setModel($popular_model);
        
        $recent_rest_model = $this->add('Model_Restaurant');
        $recent_rest_model->addCondition('city_id',$this->app->city_id);
        $recent_rest_model->addCondition('status','active');
        $recent_rest_model->addCondition('is_verified',true);

        $recent_rest_model->setOrder('id','desc')->setLimit(6);
        $recent_list = $this->add('View_Lister_Restaurant',null,'recentlyadded',['view/recentrestaurant']);
        $recent_list->setModel($recent_rest_model);

        $this->add('View_DownloadApp',null,'download_app');

        parent::recursiveRender();
    }

    function defaultTemplate(){
        return ['page/home'];
    }
}
