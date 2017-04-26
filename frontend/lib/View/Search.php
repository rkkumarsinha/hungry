<?php

class View_Search extends View{
	function init(){
		parent::init();

		$page_name = $this->app->page_object->short_name;
		
		// $tab = $this->add('Tabs');
		// $rest_tab = $tab->addTab('Restaurant');
		// $event_tab = $tab->addTab('Event');
		// $venue_tab = $tab->addTab('Venue');
		$form = $this;
		switch ($page_name) {
			case 'index':
			case 'restaurant':
				$form = $this->add('Form_Search',['redirect_page'=>'index'],'form_search');
				$this->template->trySet('tab_restaurant','active');
				break;
			case 'event':
			case 'eventdetail':
				$form = $this->add('Form_Event',['redirect_page'=>'event'],'form_search');
				$this->template->trySet('tab_event','active');
				break;
			case 'venue':
				$form = $this->add('Form_Venue',['redirect_page'=>'destination'],'form_search');
				$this->template->trySet('tab_venue','active');
				break;
			case 'destination':
			case 'destinationdetail':
				$form = $this->add('Form_Venue',['redirect_page'=>'destination'],'form_search');
				$this->template->trySet('tab_venue','active');
				break;
		}

		$this->template->trySet('restaurat_url',$this->app->getConfig('absolute_url')."index/".$this->app->city_name);
		$this->template->trySet('event_url',$this->app->getConfig('absolute_url')."event/".$this->app->city_name);
		$this->template->trySet('venue_url',$this->app->getConfig('absolute_url')."venue/".$this->app->city_name);
		// $form->layout->add('View_Location',null,'location');
	}

	function defaultTemplate(){
		return ['view/search'];
	}

}