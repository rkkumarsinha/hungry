<?php

class endpoint_index extends Endpoint_REST { 
	public $model_class = 'Restaurant';

	function init(){
		parent::init();
		
		// throw new \Exception($_GET['data']);
		$data = explode('/', $_GET['data']);

	}
	
	function get(){
		return "hello";
	}

	function get_images(){

		return 'images';
	}
}