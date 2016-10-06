<?php

class page_event extends Page{

    function init(){
        parent::init();
        
        $v = $this->add('View_Lister_HomeSlider',['city'=>$this->app->city_name,'type'=>"EventGallery"],'homeslider');
       
        $this->add('View_Search',null,'search_form');
		//Featured Restaurant
        $event_model = $this->add('Model_Event');
        $event_model->addCondition('closing_date','>=',$this->app->today);
        $event_model->setOrder('starting_date','asc');

        if($this->app->recall('event_data')){
            $data = $this->app->recall('event_data');

            $event_model->addCondition('city_id',$data['city']);
            if($data['keyword']){
                $event_model->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.trim(implode(',', explode(" ",$data['keyword'])),",").'" IN NATURAL LANGUAGE MODE)');
                $event_model->addCondition(
                                $event_model->dsql()->orExpr()
                                ->where($event_model->getElement('Relevance'),'>',0)
                                ->where($event_model->getElement('search_string'), 'like', '%'.$data['keyword'].'%')
                                );
                $event_model->setOrder('Relevance','Desc');
            }
            // throw new \Exception($event_model->count()->debug()->getOne());
            $this->app->forget('event_data');
        }else
            $event_model->addCondition('city_id',$this->app->city_id);

        $list = $this->add('View_Lister_Event',null,'eventlist');
        $list->addClass('hungryeventlister');
        $list->js('reload')->reload();
        
        $list->setModel($event_model);
    }

    function defaultTemplate(){
    	return ['page/event'];
    }
}