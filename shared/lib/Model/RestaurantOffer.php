<?php

class Model_RestaurantOffer extends SQL_Model{
	public $table = "restaurant_offer";
	function init(){
		parent::init();

		$this->hasOne('Restaurant','restaurant_id');
		$this->hasOne('Offer','offer_id');
		
		$this->addField('sub_name')->caption('Title')->mandatory(true);
		$this->addField('detail')->type('text');
		$this->addExpression('name')->set($this->dsql()->expr('[0]',[$this->getElement('sub_name')]));
		// $this->addExpression('detail')->set($this->refSQL('offer_id')->fieldQuery('detail'));
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		// $this->add('dynamic_model/Controller_AutoCreator');

	}
}

