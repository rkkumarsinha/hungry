<?php

class page_venues extends Page{

    function init(){
        parent::init();
        
		$this->api->stickyGET('city');
        $this->api->stickyGET('venue');
        
        $this->template->trySet('absolute_url',$this->app->getConfig('absolute_url'));
		// //Featured Restaurant
        $destination_model = $this->add('Model_Destination');
        $destination_model->addCondition('is_verified',true);
        $destination_model->addCondition('status','active');

         if($this->app->recall('venue_data')){
            $data = $this->app->recall('venue_data');
            $destination_model->addCondition('city_id',$data['city']);

            if($data['keyword']){
                $destination_model->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.trim(implode(',', explode(" ",$data['keyword'])),",").'" IN NATURAL LANGUAGE MODE)');
                $destination_model->addCondition(
                                $destination_model->dsql()->orExpr()
                                ->where($destination_model->getElement('Relevance'),'>',0)
                                // ->where($destination_model->getElement('search_string'), 'like', '%'.$data['keyword'].'%')
                                );
                $destination_model->setOrder('Relevance','Desc');
            }

            if($data['venue']){
                $venue_asso_j = $destination_model->join('destination_venue_association.destination_id','id');
                $venue_asso_j->addField('venue_id');
                $destination_model->addCondition('venue_id', $data['venue']);
            }

            $this->app->forget('venue_data');
        }else
            $destination_model->addCondition('city_id',$this->app->city_id);
        
        
        $list = $this->add('View_Lister_Destination',null,'destinationlist');
        $list->addClass('hungrydestinationlister');
        $list->js('reload')->reload();

        $list->setModel($destination_model);
        $this->add('View_Search',null,'search_form');

        $enquiry_form = $this->add('View_RequestToBook',null,'enquiryform');

        $js_event = [
                        $this->js()->_selector('#destination_enquiry_modalpopup')->modal('show'),
                        $enquiry_form->js()->reload(['destination_id'=>$this->js()->_selectorThis()->attr('data-destinationid')]),
                    ];
        $this->js('click',$js_event)->_selector('.hungrydestination_enquiry');
    }
    
    function defaultTemplate(){
    	return ['page/destination'];
    }
}