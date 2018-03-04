<?php

 class Model_Favorities extends SQL_Model{
 	public $table = "favorities";

	function init(){
		parent::init();
		
		$this->hasOne('User','user_id');
		$this->hasOne('Restaurant','restaurant_id');

		$this->addField('longitude');
		$this->addField('latitude');
		$this->addField('slug_url');

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->add('dynamic_model/Controller_AutoCreator');
		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if($this['restaurant_id']){
			$rest = $this->add('Model_Restaurant')->load($this['restaurant_id']);
			$this['longitude'] = $rest['longitude'];
			$this['latitude'] = $rest['latitude'];
			$this['slug_url'] = $rest['url_slug'];
		}
	}
}
 