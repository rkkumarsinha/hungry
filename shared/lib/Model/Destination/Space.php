<?php

class Model_Destination_Space extends SQL_Model{
	public $table = "destination_space";
	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');

		$this->addField('name')->mandatory(true);
		$this->addField('cps')->type('Number')->caption('Capacity of Persons')->mandatory(true);
		$this->addField('size')->mandatory(true);
		$this->addField('type')->mandatory(true);
		$this->add('filestore/Field_Image','image_id');
		$this->addField('is_active')->type('boolean')->defaultValue(true);
			
		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->getElement('image')]);
		});

		// $this->add('dynamic_model/Controller_AutoCreator');
	}	
}