<?php

class Model_RestaurantImage extends Model_Image{

	function init(){
		parent::init();

		$this->addCondition('type','restaurant');

	}
}