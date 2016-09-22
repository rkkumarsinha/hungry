<?php

class View_HostAccount_Destination_History extends View{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$this->add('View_Info')->set('History');
		// $this->setModel();
	}
}