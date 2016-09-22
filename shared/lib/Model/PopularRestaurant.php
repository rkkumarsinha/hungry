<?php

class Model_PopularRestaurant extends Model_Restaurant{

	function init(){
		parent::init();

		$this->addCondition('is_popular',true);

	}
}

