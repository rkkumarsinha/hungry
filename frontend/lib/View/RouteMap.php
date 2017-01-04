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
	public $zoom = 13;
	function init(){
		parent::init();

		
		// $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=true&language=en&key='.$this->api->getConfig('Google/MapKey'));
        $this->app->jui->addStaticInclude('gmap3.min');
	}

	function render(){
		$this->js(true)->_load('routemap')
							->routemap(
								[
									'map'=>$this->mapOptions,
									'target_latitude'=>$this->restaurant_lat,
									'target_longitude'=>$this->restaurant_lng,
									'zoom'=>$this->zoom
									]
								);
		parent::render();
	}

	function getJSID(){
		return $this->name;
	}

	function defaultTemplate(){
		return ['/view/routemap'];
	}
}