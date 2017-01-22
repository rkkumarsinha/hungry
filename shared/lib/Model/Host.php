<?php

class Model_Host extends Model_User{
	function init(){
		parent::init();

		$this->addCondition('type','host');
	}
}