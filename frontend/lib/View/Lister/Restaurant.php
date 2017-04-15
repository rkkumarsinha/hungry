<?php

class View_Lister_Restaurant extends CompleteLister{
	public $template = "view/restaurant";
	public $item_in_row = 4;
	public $show_discount = false;
		
	function init(){
		parent::init();

	}

	function setModel($model){
		parent::setModel($model);

	}

	function formatRow(){			
		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image'])?:($this->app->getConfig('absolute_url')."assets/img/hungry-not-found.jpg");
		$this->current_row['path'] = $this->api->url('restaurantdetail',['slug'=>$this->model['url_slug']]);

		// $avgcost = $this->model['avg_cost_per_person_veg'];
		// if()
		// 	$this->current_row['avgcost'] = $this->model['avg_cost_per_person_veg'];
		// else if($this->model['avg_cost_per_person_nonveg'])
		// 	$this->current_row['avgcost'] = $this->model['avg_cost_per_person_nonveg'];
		// else if($this->model['avg_cost_per_person_thali'])

		$this->current_row['avgcost'] = $this->model->avgCost();
		if($this->template->hasTag('about_restaurant_html'))
			$this->current_row_html['about_restaurant_html'] = $this->model['about_restaurant'];

		// $this->current_row['rating_percentage'] = ($this->model['rating'] /5 * 100)."%";
		
		$review = $this->add('View_Review',['restaurant_rating'=>$this->model['rating']?:0],'rating_star');
		$this->current_row_html['rating_star'] = $review->getHtml();

		if(!$this->show_discount)
			$this->current_row['getdiscount_wrapper'] = "";
		
		$this->current_row['absolute_url'] = $this->app->getConfig('absolute_url');
		parent::formatRow();

	}


	function defaultTemplate(){
		return [$this->template];
	}
}