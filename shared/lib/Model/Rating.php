<?php

class Model_Rating_to_delte extends SQL_Model{
	public $table = "rating";

	function init(){
		parent::init();

		$this->hasOne('Restaurant','restaurant_id')->mandatory(true);
		$this->hasOne('User','user_id');

		$this->addField('name');
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('is_approved')->type('boolean')->defaultValue(false);

		$this->add('dynamic_model/Controller_AutoCreator');

	}
}