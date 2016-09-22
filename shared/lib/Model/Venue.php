<?php

class Model_Venue extends SQL_Model{
	public $table = "venue";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('sequence_order')->type('number');
		$this->add('filestore/Field_Image','image_id')->mandatory(true);

		$this->hasMany('Model_Destination_VenueAssociation','venue_id');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}