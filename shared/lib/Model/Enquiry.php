<?php

class Model_Enquiry extends SQL_Model{
	public $table = "enquiry";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('name');
		$this->addField('email');
		$this->addField('mobile');
		$this->addField('subject');
		$this->addField('message')->type('text');
		$this->addField('location');

		$this->addField('status')->enum(['pending','reject','solved','progress'])->defaultValue('pending');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}