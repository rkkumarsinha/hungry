<?php

class Model_Destination_Facility extends Model_Destination_Highlight{

	function init(){
		parent::init();

		$this->addCondition('type','facility');
	}	
}