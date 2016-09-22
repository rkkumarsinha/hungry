<?php

class View_Lister_RestaurantGallery extends CompleteLister{
	public $restaurant_id;

	function init(){
		parent::init();
		
		// $gallery_model = $this->add('Model_RestaurantImage')->setLimit(1)->loadBy('restaurant_id',$this->restaurant_id);
		// $f = $this->add('filestore/Model_File')->load($gallery_model['image_id']);
		// $this->template->set('first_image',"http://localhost/hungrydunia/".str_replace("..", "", $f->getPath()));
		// $this->template->set('first_id',$gallery_model['id']);
	}

	function formatRow(){

		$f = $this->add('filestore/Model_File')->load($this->model['image_id']);
		// $path = $f->getPath();
		$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
		// $path = str_replace("/public", "", $this->model['image']);
		$this->current_row['image'] = $path;
		parent::formatRow();

	}

	function setModel($m){
		parent::setModel($m);
	}

	function defaultTemplate(){
		return ['view/restaurant/gallery'];
	}
}