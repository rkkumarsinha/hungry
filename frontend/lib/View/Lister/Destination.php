<?php

class View_Lister_Destination extends CompleteLister{
	public $template = "view/destination";
	public $item_in_row = 4;
	public $show_discount = false;
	public $header = "Destination";
		
	function init(){
		parent::init();

		// $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&language=en');
  //       $this->app->jui->addStaticInclude('gmap3.min');
        $this->template->trySet('header',$this->header);
	}

	function setModel($model){		
		parent::setModel($model);
	}

	function formatRow(){		
		$timestamp = strtotime($this->model['starting_date']);
		$day = date('d', $timestamp);
		$month = date('M', $timestamp);
				
		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image'])?:"assets/img/hungry-not-found.jpg";
		$this->current_row['path'] = $this->app->getConfig('absolute_url').'venuedetail/'.$this->model['url_slug'];
		// $this->current_row['path'] = $this->api->url('destinationdetail',['slug'=>$this->model['url_slug']]);
		$this->current_row['starting_date'] = $day;
		$this->current_row['starting_month'] = $month;

		$review = $this->add('View_Review',['restaurant_rating'=>$this->model['rating']],'rating_star');
		$this->current_row_html['rating_star'] = $review->getHtml();
		$this->current_row_html['absolute_url'] = $this->app->getConfig('absolute_url');
		parent::formatRow();
	}

	function render(){
		// $this->js(true)->_load('star-rating');
		// $this->js(true)->_load('hungry');
		parent::render();
	}

	function defaultTemplate(){
		return [$this->template];
	}
}