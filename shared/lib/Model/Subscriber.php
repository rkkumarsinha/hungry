<?php

class Model_Subscriber extends SQL_Model{
	public $table = "subscriber";

	function init(){
		parent::init();

		$this->addField('name')->caption('Email')->mandatory(true);
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d h:i:s'));

		$this->addField('mobile_no')->mandatory(true);
		$this->add('dynamic_model/Controller_AutoCreator');
		
	}

	function send(){
		if(!$this->loaded())
			throw new \Exception("model must loaded");
	
		$user = $this->add('Model_User');
		$user->send($this['email'],'Thank you for subscription',$body="Subscription Content");

	}
}