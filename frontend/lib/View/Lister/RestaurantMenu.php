<?php

class View_Lister_RestaurantMenu extends CompleteLister{
	public $restaurant_id;

	function init(){
		parent::init();
		
		
		$manu = $this->add('Model_RestaurantMenu')->addCondition('restaurant_id',$this->restaurant_id);
		if(!$manu->count()->getOne()){
			$this->add('View_Error',null,'not_found')->set('No Menu Found');
		}

		$this->setModel($manu);
	}

	function setModel($m){
		parent::setModel($m);
	}

	function formatRow(){	
			
		$f = $this->add('filestore/Model_File')->load($this->model['image_id']);
		// $path = $f->getPath();
		// $path = "http://localhost/hungrydunia/frontend/".str_replace("..", "", $f->getPath());
		$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
		
		$this->current_row['image'] = $path;		
		parent::formatRow();
	}
	
	function defaultTemplate(){
		return ['view/restaurant/menu'];
	}
}