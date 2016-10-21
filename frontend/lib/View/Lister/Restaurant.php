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
		$this->current_row['display_image'] = str_replace("/public", "", $this->model['display_image'])?:"assets/img/hungry-not-found.jpg";
		$this->current_row['path'] = $this->api->url('restaurantdetail',['slug'=>$this->model['url_slug']]);

		// $avgcost = $this->model['avg_cost_per_person_veg'];
		// if()
		// 	$this->current_row['avgcost'] = $this->model['avg_cost_per_person_veg'];
		// else if($this->model['avg_cost_per_person_nonveg'])
		// 	$this->current_row['avgcost'] = $this->model['avg_cost_per_person_nonveg'];
		// else if($this->model['avg_cost_per_person_thali'])

		$this->current_row['avgcost'] = $this->model->avgCost();
		$this->current_row['rating_percentage'] = ($this->model['rating'] /5 * 100)."%";
		
		if(!$this->show_discount)
			$this->current_row['getdiscount_wrapper'] = "";
			
		parent::formatRow();

	}


	function defaultTemplate(){
		return [$this->template];
	}
}