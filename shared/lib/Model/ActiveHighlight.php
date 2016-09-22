<?php

class Model_ActiveHighlight extends Model_Highlight{
	function init(){
		parent::init();

		$this->addCondition('is_active',true);
	}	
}