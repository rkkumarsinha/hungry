<?php

class View_Map extends View{
	public $mapOptions = ['options'=> ['center'=>[56.9348538101738,24.0438174577392],'zoom'=>11,'styles'=>[['stylers'=>[ ['saturation'=>-100],['lightness'=>20]]]] ]];

	function init(){
		parent::init();

		$this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&language=en ');
        $this->app->jui->addStaticInclude('gmap3.min');

        $apiURL = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=24.590001299999997,73.7139802&destinations=26.9000,75.8000&mode=driving&language=en-EN&sensor=false";
        $data = file_get_contents($apiURL);

        $this->add('View_Info')->set($data);

        // $this->on('change','input',function($js,$data){
        // 	// return $this->api->js()->univ()->alert('hello');
        // });
	}

	function render(){
		$this->js(true)->_load('hungrydunia')->hungrydunia(['map'=>$this->mapOptions,'restaurant_latlng'=>[56.9348538101738,24.0438174577392]]);
		parent::render();
	}

	function getJSID(){
		return "map";
	}

	function defaultTemplate(){
		return ['/view/map'];
	}
}