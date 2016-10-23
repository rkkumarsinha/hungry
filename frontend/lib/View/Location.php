<?php

class View_Location extends \View{
	function init(){
		parent::init();

		// $city = $this->add('Model_City')->addCondition('is_active',true);
		// $lister = $this->add('CompleteLister',null,'location_lister',['view/location','location_lister']);
		// $lister->setModel($city);
		// $lister->js(true)->_selector('[data-cityid='.$this->app->city_id.']')->addClass('activecity');
		// $this->template->trySet('current_location',$this->app->city_name);

		// $url = $this->app->url();
		// $this->on('click','.hungrycity',function($js,$data)use($lister,$url){
		// 	$this->app->memorize('city_id',$data['cityid']);
		// 	$js_event = [
		// 			$js->closest('.modal-body')->children('.hungrycity')->removeClass('activecity'),
		// 			$lister->js(true)->_selector('[data-cityid='.$data['cityid'].']')->addClass('activecity'),
		// 			$js->univ()->location($url)
		// 		];
		// 	return $js_event;
		// });
	}

	function defaultTemplate(){
		return ['view/location'];
	}
}