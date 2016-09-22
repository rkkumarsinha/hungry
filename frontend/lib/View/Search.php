<?php

class View_Search extends View{
	function init(){
		parent::init();

		$page_name = $this->app->page_object->short_name;

		// $tab = $this->add('Tabs');
		// $rest_tab = $tab->addTab('Restaurant');
		// $event_tab = $tab->addTab('Event');
		// $venue_tab = $tab->addTab('Venue');

		switch ($page_name) {
			case 'index':
				$this->add('Form_Search',null,'form_search');
				$this->template->trySet('tab_restaurant','active');
				break;
			case 'event':
				$this->add('Form_Event',null,'form_search');
				$this->template->trySet('tab_event','active');
				break;
			case 'venue':
				$this->add('Form_Venue',['redirect_page'=>'destination'],'form_search');
				$this->template->trySet('tab_venue','active');
				break;
			case 'destination':
				$this->add('Form_Venue',null,'form_search');
				$this->template->trySet('tab_venue','active');
				break;
		}
	}

	function defaultTemplate(){
		return ['view/search'];
	}

}