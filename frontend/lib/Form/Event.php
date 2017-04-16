<?php

class Form_Event extends Form{
    public $city_id=false;
    public $btn_clicked = false;
    public $redirect_page = 'event';
    function init(){
        parent::init();
        
        $this->setLayout('form/search');
        $this->addClass('hungry-form-search');

        $search_phrase = $this->addField('autocomplete\Form_Field_RelavanceBasic',
                                        [
                                            'search_field' =>'name',
                                            'name'=>'keyword',
                                            'hint'=>'Search by event name or location',
                                            'options'=>['mustMatch'=>false]
                                        ]);

        $event_model = $this->add('Model_Event');
        $event_model->addCondition('city_id',$this->app->city_id);
        $event_model->addCondition('is_active',true);
        $event_model->addCondition('is_verified',true);
        $event_model->addCondition('closing_date','>=',$this->app->today);
        
        $search_phrase->setModel($event_model);

        // if($_GET['form_city_id']){
        //     $this->app->memorize('form_city_id',$_GET['form_city_id']);
        // }
        
        //City Dropdown
        $city_f = $this->addField('DropDown','city');
        $city_f->setEmptyText('Select City');
        $city_model = $this->add('Model_City')->addCondition('is_active',true)->setOrder('name');
        $city_f->setModel($city_model);
        if($this->api->city_id)
            $city_f->set($this->app->city_id);

        // $js_event = [                    
        //             $search_phrase->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$search_phrase->name]),'form_city_id'=>$city_f->js()->val()])
        //         ];
        // $city_f->js('change',$js_event);

        $city_f->js('change')->submit();

        $this->js('click')->_selector('.atk-swatch-orange.do-search')->submit();

        if($this->isSubmitted()){
            if(!$this['city'])
                $this->error('city','must select city');

            if($this->app->city_id != $this['city'])  {
                $this->app->memorize('city_id',$this['city']);
                $this->app->redirect($this->app->url($this->redirect_page,['city'=>$this->app->active_city[$this['city']]]));
            }
            
            $event_model = $this->add('Model_Event')->tryLoad($this['keyword']);
            if($event_model->loaded()){
                $this->app->redirect($this->app->url('eventdetail',['slug'=>$event_model['url_slug']]));
            }

             $search_term = 0;
            if($this['keyword']){
                $search_term = $this['keyword'];
            }else{
                $search_term = $this->app->recall('search_term');
                // $this->app->forget('search_term');
            }

            $event_data = [
                            'city' => $this['city'],
                            'keyword' => $search_term
                        ];
            $this->app->memorize('event_data',$event_data);

            $this->js()->_selector('.hungryeventlister')->trigger('reload')->execute();
            // $this->app->redirect($this->app->url('event'));
        }
    }

    // function defaultTemplate(){
    //     return ['view\homesearch'];
    // }
}
