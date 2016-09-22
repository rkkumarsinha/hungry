<?php

class page_user extends Page{
	function init(){
		parent::init();

		$this->add('CRUD')->setModel('User',['name','email','type','verification_code','password']);


	}
}