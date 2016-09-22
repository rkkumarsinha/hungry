<?php

class Model_EventImage extends Model_Image{

	function init(){
		parent::init();

		$this->addCondition('type','event');

	}
}