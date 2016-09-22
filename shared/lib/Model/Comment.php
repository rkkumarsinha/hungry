<?php

class Model_Comment extends SQL_Model{
	public $table="comment";
	function init(){
		parent::init();

		$this->hasOne('User','user_id'); //always owner of restaurant
		$this->hasOne('Review','review_id');
		
		$this->addExpression('profile_image_url')->set($this->refSQL('user_id')->fieldQuery('profile_image_url'));
		$this->addExpression('user_profile')->set($this->refSQL('user_id')->fieldQuery('profile_image_url'));
		
		$this->addField('created_at')->type('datetime');
		$this->addField('is_approved')->type('boolean');
		$this->addField('comment')->type('text');

		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if(!$this['created_at'])
			$this['created_at'] = $this->api->today;
	}


}