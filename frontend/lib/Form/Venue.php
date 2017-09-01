<?php

class Form_Venue extends Form{

    public $city_id=false;
    public $btn_clicked = false;
    public $redirect_page;
        
    function init(){
        parent::init();

        $this->setLayout('form/venuesearch');
        $this->api->stickyGET('form_city_id');
        $this->api->stickyGET('form_venue_id');

        $this->addClass('hungry-form-search');
        $city_id = $this->app->city_id;
        if($_GET['form_city_id'])
            $city_id = $_GET['form_city_id'];



        $search_phrase = $this->addField('autocomplete\Form_Field_SearchBasic',
                                    [
                                        'search_field'=>'search_string',
                                        'name'=>'keyword',
                                        'hint'=>'Search by venue, destination name or location',
                                        'options'=>['mustMatch'=>false]
                                    ]);

        $destination_model = $this->add('Model_Destination');
        $venue_asso_j = $destination_model->Join('destination_venue_association.destination_id',null,null,'dvass');
        $venue_asso_j->addField('venue_id');
        $destination_model->addCondition('city_id',$city_id);
        $destination_model->addCondition('status','active');
        $destination_model->addCondition('is_verified',true);
        $destination_model->_dsql()->group('id');
        $search_phrase->setModel($destination_model);

        $venue_id = $_GET['venue'];
        if(!is_numeric($venue_id)){
            $venue_id = $this->add('Model_Venue')->addCondition('name',$venue_id)->tryLoadAny()->id;
        }

        if($this->app->recall('form_venue_id')){
            $venue_id = $this->app->recall('form_venue_id');
            $this->app->memorize('form_venue_id',$venue_id);
        }
        
        if($_GET['form_venue_id']){
            $venue_id = $_GET['form_venue_id'];
            $this->app->memorize('form_venue_id',$venue_id);
        }

        if($_GET['form_city_id']){
            $this->app->memorize('form_city_id',$_GET['form_city_id']);
        }
        
        //City Dropdown
        $city_f = $this->addField('DropDown','city','');
        $city_f->setEmptyText('Select City');
        $city_model = $this->add('Model_City')
                        ->setOrder('name')->addCondition('is_active',true);
        $city_f->setModel($city_model);
        $city_f->set($city_id);

        // venue Dropdown
        $venue_f = $this->addField('DropDown','venue');
        $venue_f->setEmptyText('Select Venue');
        $venue_model = $this->add('Model_Venue')->setOrder('name');
        $venue_f->setModel($venue_model);
        
        $venue_f->set($venue_id?:0);
        
        $venue_js = [
                    $search_phrase->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$search_phrase->name]),'form_venue_id'=>$venue_f->js()->val()])
                ];
        $venue_f->js('change',$venue_js);

        // $venue_f->js('change',$this->js()->atk4_form('reloadField','keyword',[$this->app->url(),'form_venue_id'=>$venue_f->js()->val()]));

        $js_event = [
                    $search_phrase->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$search_phrase->name]),'form_city_id'=>$city_f->js()->val()])
                ];
        $city_f->js('change',$js_event);

        $this->js('click')->_selector('.atk-swatch-orange.do-search')->submit();
        if($this->isSubmitted()){

            if(!$this['city'])
                $this->error('city','must select city');

            // keyword actually the destination
            if(is_numeric($this['keyword'])){
                $destination_model = $this->add('Model_Destination')->tryLoad($this['keyword']);
                if($destination_model->loaded())               
                    $this->app->redirect($this->app->url('venuedetail',['slug'=>$destination_model['url_slug']]));
            }

            $venue_data = [
                            'city'=>$this['city'],
                            'venue'=>$this['venue'],
                            'keyword'=>$this['keyword']
                        ];
            $this->app->memorize('venue_data',$venue_data);
                                               
            if(!$this['venue']){
                $this->app->stickyForget('venue');
                $this->redirect_page = "venue";
                $url = $this->app->url($this->redirect_page,['city'=>$this->app->active_city[$this['city']]]);
            }else{
                $venue_name = $this->add('Model_Venue')->load($this['venue'])['name'];
                $url = $this->app->url($this->redirect_page,['city'=>$this->app->active_city[$this['city']] ,'venue'=>$venue_name]);
            }
            
            if($this->redirect_page){
                $this->app->forget('form_venue_id');
                $this->app->redirect($url);
            }else{
                $this->js()->_selector('.hungrydestinationlister')->trigger('reload')->execute();
            }

        }

    }
}
