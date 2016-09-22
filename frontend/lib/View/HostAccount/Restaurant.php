<?php

class View_HostAccount_Restaurant extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		$this->api->stickyGET('selectedmenu');

		if(!isset($this->app->listmodel->id)){
			$this->owner->add('View_Info',null,'list_data')->set($this->app->listmodel['name']);
		}

		$selected_view = $_GET['selectedmenu']?:'DiscountCoupon';
		$this->add('View_HostAccount_Restaurant_'.$selected_view);
		$this->js(true)->_selector('.hostaccount-verticaltabs[data-type="'.$selected_view.'"]')->addClass('active');
		
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);
		// $this_url = $this->api->url(null,['cut_object'=>$right->name]);
		$this->on('click','.hostaccount-verticaltabs',function($js,$data)use($this_url){
			$js = [
					$this->js()->reload(['selectedmenu'=>$data['type']],null,$this_url)
                ];
            return $js;
		});
	}

	function defaultTemplate(){
		return ['view\hostaccount\restaurant'];
	}
}