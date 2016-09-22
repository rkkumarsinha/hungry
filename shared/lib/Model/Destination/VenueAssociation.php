<?php

class Model_Destination_VenueAssociation extends SQL_Model{
	public $table = "destination_venue_association";
	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->hasOne('Venue','venue_id');
		
		$this->addExpression('name')->set($this->refSQL('venue_id')->fieldQuery('name'));
		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->refSQL('venue_id')->fieldQuery('image')]);
		});

		$this->addHook('afterSave',[$this,'updateSearchString']);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function updateSearchString(){
		$this->add('Model_Destination')->load($this['destination_id'])->updateSearchString();
	}

}