<?php

class Model_City extends SQL_Model{
	public $table = "city";

	function init(){
		parent::init();

		$this->hasOne('State')->mandatory(true);
		$this->addField('name')->mandatory(true);
		$this->addField('is_active')->type('boolean')->defaultValue(false);

		$this->addExpression('country',function($m){
			return $m->refSQL('state_id')->fieldQuery('country');
		});

		$this->addField('latitude');
		$this->addField('longitude');

		$this->hasMany('Area','city_id');
		$this->hasMany('Image','city_id');
		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('afterSave',$this);
	}

	function afterSave(){

		$city = $this->add('Model_City');
		foreach ($city as $junk) {
			$dir_path = '../json/'.strtoupper($junk['name']);
			if(!file_exists($dir_path))
				mkdir($dir_path, 0755, true);
		}
	}


}