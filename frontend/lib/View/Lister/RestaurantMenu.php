<?php

class View_Lister_RestaurantMenu extends CompleteLister{
	public $restaurant_id;

	function init(){
		parent::init();
		
		
		$manu = $this->add('Model_RestaurantMenu')
				->addCondition('restaurant_id',$this->restaurant_id)
				->addCondition('is_active',true)
				->addCondition('status','approved')
				;

		if(!$manu->count()->getOne()){
			$this->add('View_Info',null,'not_found')->set('we are collecting images');
		}

		$this->template->trySet('absolute_url',$this->app->getConfig('absolute_url'));
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
		$this->current_row['absolute_url'] = $this->app->getConfig('absolute_url');

		parent::formatRow();
	}
	
	function defaultTemplate(){
		return ['view/restaurant/menu'];
	}
}