<?php

class Model_BlogCategory extends SQL_Model{
	public $table = "blog_category";

	function init(){
		parent::init();

		$this->hasOne('City','city_id');
		$this->addField('name')->mandatory(true);
		$this->addField('is_active')->type('boolean')->defaultValue(1);
		
		$this->add('dynamic_model/Controller_AutoCreator');

	}
}