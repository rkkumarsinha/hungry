<?php

class Model_Destination_Service extends Model_Destination_Highlight{

	function init(){
		parent::init();

		$this->addCondition('type','service');
	}	
}