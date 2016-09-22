<?php

class View_HostAccount_Destination extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		$this->add('View_Info')->set("Destination Dashboard");
	}

	function defaultTemplate(){
		return ['view\hostaccount\destination'];
	}
}