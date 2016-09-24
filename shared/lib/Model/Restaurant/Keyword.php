<?php

class Model_Restaurant_Keyword extends SQL_Model{
	public $table = "restaurant_keyword_association";
	function init(){
		parent::init();

		$this->hasOne('Restaurant');
		$this->hasOne('Keyword');
		
		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->refSQL('keyword_id')->fieldQuery('image')]);
			 // $m->refSQL('keyword_id')->fieldQuery('image');
		});
		
		$this->addHook('afterSave',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterSave(){
		$this->add('Model_Keyword')->updateKeywordJson();

		$this->add('Model_Restaurant')->load($this['restaurant_id'])->updateSearchString();
	}
}