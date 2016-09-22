<?php

class Model_CategoryAssociation extends SQL_Model{
	public $table = "category_restaurant_asso";

	function init(){
		parent::init();

		$this->hasOne('Restaurant','restaurant_id');
		$this->hasOne('Category','category_id');
	
		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->refSQL('category_id')->fieldQuery('image')]);
			// return $m->refSQL('Highlight_id')->fieldQuery('image');
		});

		$this->addHook('afterSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterSave(){
		$this->add('Model_Restaurant')->load($this['restaurant_id'])->updateSearchString();
	}
	
}