<?php

class View_Lister_PopularRestaurant extends CompleteLister{
	
	function init(){
		parent::init();
	}

	function setModel($model){
		parent::setModel($model);

	}

	function formatRow(){
		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image']);
		$this->current_row['path'] = $this->api->url('restaurantdetail',['slug'=>$this->model['url_slug']]);
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/popularrestaurant'];
	}
}