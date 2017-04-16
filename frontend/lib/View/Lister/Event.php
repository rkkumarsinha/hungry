<?php

class View_Lister_Event extends CompleteLister{
	public $template = "view/event";
	public $item_in_row = 4;
	public $show_discount = false;
	public $header = "Recent";
		
	function init(){
		parent::init();

		// $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&language=en');
        // $this->app->jui->addStaticInclude('gmap3.min');
        $this->template->trySet('header',$this->header);
	}

	function setModel($model){		
		parent::setModel($model);
	}

	function formatRow(){		
		$timestamp = strtotime($this->model['starting_date']);
		$day = date('d', $timestamp);
		$month = date('M', $timestamp);
		
		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image']);
		$this->current_row['path'] = $this->app->getConfig('absolute_url').'eventdetail/'.$this->model['url_slug'];
		// $this->current_row['path'] = $this->api->url('eventdetail',['slug'=>$this->model['url_slug']]);

		$this->current_row['starting_date'] = $day;
		$this->current_row['starting_month'] = $month;

		if($this->model['remaining_tickets'])
			$this->current_row_html['bookticket_wrapper'] = '<a href="?page=bookticket&slug='.$this->model['url_slug'].'" data-restaurantid="'.$this->model->id.'" style="width:60%;margin-top:10px;border-radius:10px;" class="btn-block atk-swatch-orange btn hungry-getdiscount container">Book Ticket</a>';
		else{
			$this->current_row_html['bookticket_wrapper'] = '<div style="height:48px;"></div>';
		}

		$this->current_row_html['absolute_url'] = $this->app->getConfig('absolute_url');
		parent::formatRow();
	}

	function defaultTemplate(){
		return [$this->template];
	}
}