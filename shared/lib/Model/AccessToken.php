<?php

class Model_AccessToken extends SQL_Model{
	public $table = "accesstoken";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('social_app')->enum(['Facebook','Google','HungryDunia']);
		$this->addField('social_access_token')->type('text');
		$this->addField('access_token_expire_on')->type('DateTime');
		$this->addField('return_userid');
		$this->addField('social_content')->type('text');
		$this->addField('profile_picture_url')->type('text');

		$this->add('dynamic_model/Controller_AutoCreator');

	}
}