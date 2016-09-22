<?php

class Model_DestinationImage extends Model_Image{

	function init(){
		parent::init();

		$this->addCondition('type','destination');

	}
}