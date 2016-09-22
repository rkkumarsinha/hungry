<?php

class View_RouteMap extends View{

	public $mapOptions = [
							'options'=> [
											'center'=>[56.9348538101738,24.0438174577392],
											'zoom'=>11,
											'styles'=>[
															['stylers'=>[ 
																			['saturation'=>-100],
																			['lightness'=>20]
																		]
															]
														] 
										]
						];

	public $restaurant_lat;
	public $restaurant_lng;
	function init(){
		parent::init();

		$this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&language=en');
        $this->app->jui->addStaticInclude('gmap3.min');

	}

	function render(){
		$this->js(true)->_load('routemap')
							->routemap(
								[
									'map'=>$this->mapOptions,
									'target_latitude'=>$this->restaurant_lat,
									'target_longitude'=>$this->restaurant_lng
									]
								);
		parent::render();
	}

	function getJSID(){
		return "routemap";
	}

	function defaultTemplate(){
		return ['/view/routemap'];
	}
}