<?php

class View_HostAccount_Event_Verified extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		$event_model = $this->app->listmodel;
		if(!$event_model->loaded()){
			$this->add('View_Error')->set('no record found');
			return;
		}
		
		
		$model = $this->add('Model_UserEventTicket');		
		$model->addExpression('event_name')->set($model->refSQL('event_ticket_id')->fieldQuery('event_name'));
		$model->addExpression('event_id')->set($model->refSQL('event_ticket_id')->fieldQuery('event_id'));
		// $model->addCondition('event_id',$event_model->id);
		$model->addCondition('is_verified',true);
		$model->setOrder('id','desc');
		$model->getElement('ticket_booking_no')->caption('Booking No');
		if(!$model->count()->getOne()){
			$this->add('View_Error')->set('no record found');
			return;
		}

		$grid = $this->add('Grid')->addClass('atk-box');//,null,'bookedticket',['view\hostaccount\event\bookedticket','bookedticket']);
		$grid->setModel($model,['ticket_booking_no','booking_name','qty','status','mobile','email','narration']);
		$grid->addQuickSearch(['ticket_booking_no','booking_name','qty','status','mobile','email']);
		// $lister->addHook('formatRow',function($l){
		// 	$l->current_row_html['event_image'] = str_replace("frontend", "", $l->model['event_image']);
		// });

		// $this_url = $this->api->url(null,['cut_object'=>$this->name]);
		// $this->on('click','.eventactiontype',function($js,$data)use($this_url){
		// 	$this->add('Model_UserEventTicket')->load($data['verifyticket'])->verify();
		// 	return [$this->js()->reload(null,null,$this_url)];
		// });

	}

	function render(){		
		parent::render();
	}

}