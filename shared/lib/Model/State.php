<?php

class Model_State extends SQL_Model{
	public $table = "state";

	function init(){
		parent::init();

		$this->hasOne('Country')->mandatory(true);

		$this->addField('name')->mandatory(true);

		$this->addField('latitude');
		$this->addField('longitude');
		$this->addField('is_active')->type('boolean');
		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}