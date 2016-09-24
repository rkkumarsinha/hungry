<?php

class Model_EventDestinationRest extends SQL_Model{
	public $table = "event_destination_restaurant_asso";

	function init(){
		parent::init();

		$this->hasOne('Event','event_id');
		$this->hasOne('Destination','destination_id');
		$this->hasOne('Restaurant','restaurant_id');
		$this->addField('type')->setValueList(['event'=>'event','destination'=>'destination','restaurant'=>'restaurant']);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}