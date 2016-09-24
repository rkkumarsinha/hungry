<?php

class Model_Country extends SQL_Model{
	public $table = "country";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('latitude');
		$this->addField('longitude');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}