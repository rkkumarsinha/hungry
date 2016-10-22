<?php

class Model_Discount extends SQL_Model{
	public $table = "discount";
	
	function init(){
		parent::init();

		$this->addField('name')->type('Number')->mandatory(true);

		$this->hasMany('Restaurant','discount_id');
		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}

