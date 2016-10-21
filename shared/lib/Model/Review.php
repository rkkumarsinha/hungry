<?php

class Model_Review extends SQL_Model{
	public $table = "review";

	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->hasOne('Restaurant','restaurant_id');
		$this->hasOne('User','user_id');

		$this->addExpression('profile_image_url')->set($this->refSQL('user_id')->fieldQuery('profile_image_url'));
		$this->addExpression('user_profile')->set($this->refSQL('user_id')->fieldQuery('profile_image_url'));
		$this->addExpression('user_name')->set($this->refSQL('user_id')->fieldQuery('name'));

		$this->addField('rating')->type('Number');
		$this->addField('title')->mandatory(true);
		$this->addField('comment')->type('text')->mandatory(true);
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('created_time')->type('time')->defaultValue(date('H:i:s'));
		
		$this->addField('is_approved')->type('boolean')->defaultValue(false);

		$this->hasMany('Comment','review_id');

		// $this->add('dynamic_model/Controller_AutoCreator');

	}
}