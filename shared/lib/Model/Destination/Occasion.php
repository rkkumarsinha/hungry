<?php

class Model_Destination_Occasion extends Model_Destination_Highlight{

	function init(){
		parent::init();

		$this->addCondition('type','occasion');
	}	
}