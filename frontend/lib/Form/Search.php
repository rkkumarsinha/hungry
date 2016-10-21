<?php

// Search Form used for Restaurant Search

class Form_Search extends Form{
    public $city_id=false;
    public $btn_clicked = false;
    public $redirect_page = 'index';
    function init(){
        parent::init();
        
        // $this->js(true)->_load('selectize');

        $this->setLayout('form/search');
        // $this->api->stickyGET('form_city_id');
        // $this->layout->add('View_Location',null,'location');

        $search_phrase = $this->addField('autocomplete\Form_Field_RelavanceBasic',
                                        [
                                            'search_field' =>'search_string',
                                            'name'=>'keyword',
                                            'hint'=>'Search by cuisine, restaurant name, location',
                                            'options'=>['mustMatch'=>false]
                                        ]);

        // $search_phrase = $this->addField('SelectizeDropDown','keyword');
        $restaurant_model = $this->add('Model_Restaurant');
        $restaurant_model->addCondition('city_id',$this->app->city_id);
        $search_phrase->setModel($restaurant_model);
        
        // if($_GET['form_city_id']){            
        //     $this->app->memorize('form_city_id',$_GET['form_city_id']);
        // }
        
        //City Dropdown
        $city_f = $this->addField('DropDown','city');
        $city_f->setEmptyText('Select City');
        $city_model = $this->add('Model_City')->addCondition('is_active',true);
        $city_f->setModel($city_model);
        if($this->api->city_id)
            $city_f->set($this->api->city_id);
        
        // $js_event = [
        //             $search_phrase->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$search_phrase->name]),'form_city_id'=>$city_f->js()->val()])
        //         ];

        $city_f->js('change')->submit();

        $this->js('click')->_selector('.atk-swatch-orange.do-search')->submit();
        if($this->isSubmitted()){
            if($this->app->city_id != $this['city'])  {
                $this->app->memorize('city_id',$this['city']);
                $this->app->redirect($this->app->url($this->redirect_page));
            }

            $restro_id = 0;
            $search_term = 0;
            if($this['keyword']){
                $restro_id = $this['keyword'];
            }else{
                $search_term = $this->app->recall('search_term');
                $this->app->forget('search_term');
            }
            
            if(!$this['city'] and !($restro_id or $search_term))
                $this->error('city','must select city');
            
            // keyword actually the restaurant
            if($restro_id > 0){
                $rest_model = $this->add('Model_Restaurant')->tryLoad($restro_id);
                if($rest_model->loaded()){
                    $this->app->redirect($this->app->url('restaurantdetail',['slug'=>$rest_model['url_slug']]));
                }
            }

            $search_data = [
                            'city'=>$this['city'],
                            'keyword'=>$search_term?:$restro_id
                        ];
            $this->app->memorize('search_data',$search_data);

            $this->app->redirect($this->app->url('search'));
        }
    }
}
