<?php

class Model_Offer extends SQL_Model{
	public $table = "offer";
	
	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('detail')->type('text');

		$this->hasMany('RestaurantOffer','offer_id');
		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}

