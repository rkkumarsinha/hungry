<?php

class View_SearchMap extends View{

	public $mapOptions = [
							'options'=> [
											'center'=>[73.66686549999997,24.6279807],
											'zoom'=>3,
											'styles'=>[
												['stylers'=>[ 
														['saturation'=>-100],
														['lightness'=>20]
													]
												]
											] 
										]
						];

	function init(){
		parent::init();
		
		$this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&language=en');
        $this->app->jui->addStaticInclude('gmap3.min');

	}

	function render(){
		$this->js(true)->_load('searchmap')
						->searchmap(['map'=>$this->mapOptions]);
		parent::render();
	}

	function getJSID(){
		return "searchmap";
	}

	function defaultTemplate(){
		return ['/view/searchmap'];
	}
}