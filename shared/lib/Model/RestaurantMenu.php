<?php

class Model_RestaurantMenu extends Model_Image{

	function init(){
		parent::init();

		$this->addCondition('type','menu');

	}
}