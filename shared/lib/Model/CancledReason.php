<?php

class Model_CancledReason extends SQL_Model{
	public $table = "cancled_reason";

	function init(){
		parent::init();
		
		$this->addField('name')->mandatory(true);

		$this->add('dynamic_model/Controller_AutoCreator');

	}
}