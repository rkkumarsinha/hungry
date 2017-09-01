<?php

class page_venue extends Page{

    function init(){
        parent::init();
		
		if($this->app->recall('from_venue_id'))
			$this->app->forget('from_venue_id');
		if($this->app->recall('from_venue_id'))
			$this->app->forget('from_city_id');

		$this->api->stickyGET('city');
		
		$v = $this->add('View_Lister_HomeSlider',['city'=>$this->app->city_name,'type'=>"VenueGallery"],'homeslider');
        $this->add('View_Search',null,'search_form');

		$lister = $this->add('CompleteLister',null,'venue',['page/venue','lister_wrapper']);
		$model = $this->add('Model_Venue')->setOrder('sequence_order','asc');
		$model->addExpression('venue_image_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->getElement('image')]);
			// return $m->refSQL('Highlight_id')->fieldQuery('image');
		});

		$model->addExpression('absolute_url')->set(function($m,$q){
			return $q->expr("CONCAT('[0]','[1]',[2])",[$m->app->getConfig('absolute_url'),('destination/'.$m->app->city_name."/"),$m->getElement('name')]);
		});
		$lister->setModel($model);

    }

    function defaultTemplate(){
    	return ['page/venue'];
    }
}