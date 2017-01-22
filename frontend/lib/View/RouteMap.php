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

		// <iframe src="https://www.google.com/maps/embed/v1/directions?key=AIzaSyC8mla9J14yiKhGaeDrX0EUHejZuLq2nkQ&origin=My%20Location&destination=24.586885,73.69917499999997" style="width:100%;height:500px;" allowfullscreen></iframe>
		// $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=true&language=en&key='.$this->api->getConfig('Google/MapKey'));
        // $this->app->jui->addStaticInclude('gmap3.min');
		
		$embed_iframe = $this->add('View')->setElement('iframe');
		$embed_iframe->setAttr('src','https://www.google.com/maps/embed/v1/directions?key=AIzaSyC8mla9J14yiKhGaeDrX0EUHejZuLq2nkQ&origin=My%20Location&destination='.$this->restaurant_lat.','.$this->restaurant_lng);
		$embed_iframe->setAttr('allowfullscreen',true);
		$embed_iframe->setStyle(['height'=>'300px','width'=>'100%','border'=>'0px']);

		// $b = $this->add('Button')->set('View Large');
		// $b->js('click')->univ()->frameURL($this->app->url('test'));
	}

	// function render(){
	// 	$this->js(true)->_load('routemap')
	// 						->routemap(
	// 							[
	// 								'map'=>$this->mapOptions,
	// 								'target_latitude'=>$this->restaurant_lat,
	// 								'target_longitude'=>$this->restaurant_lng,
	// 								'zoom'=>$this->zoom
	// 								]
	// 							);
	// 	parent::render();
	// }

	// function getJSID(){
	// 	return $this->name;
	// }

	// function defaultTemplate(){
	// 	return ['/view/routemap'];
	// }
}