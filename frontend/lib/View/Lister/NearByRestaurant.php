<?php

class View_Lister_NearByRestaurant extends CompleteLister{
	
	function setModel($model){
		parent::setModel($model);

	}

	function formatRow(){
		parent::formatRow();

		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image']);
		$this->current_row['path'] = $this->api->url('restaurantdetail',['slug'=>$this->model['url_slug']]);
	}

	function defaultTemplate(){
		return ['view/nearbyrestaurant'];
	}
}