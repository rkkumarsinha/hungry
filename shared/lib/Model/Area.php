<?php

class Model_Area extends SQL_Model{
	public $table = "area";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('is_city')->type('boolean')->defaultValue(false);
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->hasOne('City')->mandatory(true);

		$this->addExpression('state',function($m){
			return $m->refSQL('city_id')->fieldQuery('state');
		});

		$this->addExpression('country',function($m){
			return $m->refSQL('city_id')->fieldQuery('country');
		});

		$this->addField('latitude');
		$this->addField('longitude');

		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('afterSave',$this);
	}

	function afterSave(){		
		$filename = "../json/".strtoupper($this['city'])."/area.json";

		$area = $this->add('Model_Area')->addCondition('city_id',$this['city_id']);
		file_put_contents($filename, json_encode($area->getRows()));
	}


}