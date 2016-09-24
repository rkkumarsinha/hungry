<?php

class Model_Restaurant_Highlight extends SQL_Model{
	public $table = "restaurant_highlight_association";
	function init(){
		parent::init();

		$this->hasOne('Restaurant');
		$this->hasOne('ActiveHighlight');

		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->refSQL('Highlight_id')->fieldQuery('image')]);
			// return $m->refSQL('Highlight_id')->fieldQuery('image');
		});

		$this->addExpression('is_active')->set(function($m,$q){
			return $m->refSQL('Highlight_id')->fieldQuery('is_active');
		});

		$this->addHook('afterSave',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterSave(){		
		$this->add('Model_Restaurant')->load($this['restaurant_id'])->updateSearchString();
	}
}