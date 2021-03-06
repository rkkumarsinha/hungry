<?php

class View_Review extends View{
	public $restaurant_id=null;
	public $restaurant_rating=0;
	public $comment_id=0;

	function init(){
		parent::init();

		// if(!$this->restaurant_id or !is_numeric($this->restaurant_id))
		// 	throw new \Exception("must pass proper restaurant");
		
		if(!$this->restaurant_rating or !is_numeric($this->restaurant_rating)){
			$this->restaurant_rating = 0;
			// throw new \Exception("must pass proper rating value");
		}

		if(!$this->comment_id and !is_numeric($this->comment_id))
			throw new \Exception("must pass proper comment value");

		$this->template->trySet('rating',($this->restaurant_rating/5*100)."%");
		// $this->add('View')->setHtml("<input class='rating-loading' value='$this->restaurant_rating'/>")->addClass('hungryrating');
	}

	// function render(){
		
		// $this->js()->_load('hungry');
		// $this->js(true)->univ()->hungryrating($this->restaurant_id,$this->comment_id,false);

		// parent::render();
	// }

	function defaultTemplate(){
		return ['view/review'];
	}
}