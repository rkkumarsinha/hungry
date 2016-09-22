<?php

class View_HostAccount_Destination_Sales extends View{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$this->add('View_Info')->set('Sales account');
		// $this->setModel();
	}
}