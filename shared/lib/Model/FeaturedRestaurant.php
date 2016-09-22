<?php

class Model_FeaturedRestaurant extends Model_Restaurant{

	function init(){
		parent::init();

		$this->addCondition('is_featured',true);

	}
}

