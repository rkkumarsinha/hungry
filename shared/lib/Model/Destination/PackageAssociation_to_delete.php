<?php

class Model_Destination_PackageAssociation extends SQL_Model{
	public $table = "destination_package_association";
	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->hasOne('Destination_Package','destination_package_id');

		$this->addExpression('package_name')->set(function($m,$q){
			return $m->refSQL('destination_package_id')->fieldQuery('name');
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}