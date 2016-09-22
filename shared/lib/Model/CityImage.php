<?php

class Model_CityImage extends Model_Image{

	function init(){
		parent::init();

		$this->addCondition('type','city');
		
	}
}