<?php
class page_index extends Page
{
    function init(){
        parent::init();
        
        $v = $this->add('View_Lister_HomeSlider',['city'=>$this->app->city_name,'type'=>"RestaurantGallery"],'homeslider');
        $this->add('View_Search',null,'search_form');
    }

    function recursiveRender(){

        //Featured Restaurant
        $featured_rest_model = $this->add('Model_FeaturedRestaurant');
        $featured_rest_model->addCondition('city_id',$this->app->city_id);
        
        $featured_rest_model->setOrder('id','desc')->setLimit(4);
        $list = $this->add('View_Lister_Restaurant',null,'restaurantlist');
        $list->setModel($featured_rest_model);

        //Popular This Week Restaurant
        $popular_model = $this->add('Model_PopularRestaurant');
        $popular_model->addCondition('city_id',$this->app->city_id);
        $popular_model->setOrder('id','desc')->setLimit(4);
        $p_list = $this->add('View_Lister_Restaurant',['template'=>'view/popularrestaurant'],'popularrestaurant');
        $p_list->setModel($popular_model);
        
        $recent_rest_model = $this->add('Model_Restaurant');
        $recent_rest_model->addCondition('city_id',$this->app->city_id);
        $recent_rest_model->setOrder('id','desc')->setLimit(6);
        $recent_list = $this->add('View_Lister_Restaurant',null,'recentlyadded',['view/recentrestaurant']);
        $recent_list->setModel($recent_rest_model);

        parent::recursiveRender();
    }

    function defaultTemplate(){
        return ['page/home'];
    }
}
