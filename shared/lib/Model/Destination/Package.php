<?php

class Model_Destination_Package extends SQL_Model{
	public $table = "destination_package";
	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->addField('name')->mandatory(true);
		$this->addField('price')->type('money')->mandatory(true);
		$this->addField('detail')->type('text')->mandatory(true);
		
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		

		$this->add('dynamic_model/Controller_AutoCreator');
	}	
}