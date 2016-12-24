<?php

class View_HostAccount_Event_ManageTicket extends View{
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			return;
		}

		$event_model = $this->app->listmodel;
		if(!$event_model->loaded()){
			$this->add('View_Error')->set('no record found');
			return;
		}
		// Ticket
		$form = $this->add('Form')->addClass('atk-box');
		$field_event_day = $form->addField('DropDown','event_day');
		$field_event_day->setModel($this->add('Model_Event_Day')->addCondition('event_id',$event_model->id));
		$field_event_day->setEmptyText('Please Select Day');

		$field_event_time = $form->addField('DropDown','event_time');
		$field_event_time->setModel($this->add('Model_Event_Time')->addCondition('event_id',$event_model->id));
		$field_event_time->setEmptyText('Please Select Time');

		$ticket_crud = $this->add('CRUD');
		$ticket_model = $this->add('Model_Event_Ticket')
							->addCondition('event_id',$event_model->id)
							;
		$ticket_model->addExpression('sold')->set($ticket_model->refSQL('UserEventTicket')->addCondition('status','paid')->sum('qty'))->type('int');
							
		if($_GET['event_time']){
			$ticket_model->addCondition('event_time_id',$_GET['event_time']);
		}
		if($_GET['event_day']){
			$ticket_model->addCondition('event_day_id',$_GET['event_day']);
		}

		$ticket_model->setOrder('id','desc');

		$ticket_crud->setModel($ticket_model,
										['event_id','event_time_id','name','price','detail','max_no_to_sale','disclaimer','is_voucher_applicable'],
										['name','price','offer_percentage','is_voucher_applicable']
									);
		
		if($ticket_crud->isEditing()){
			$ticket_crud->form->getElement('event_time_id')->getModel()->addCondition('event_id',$event_model->id);
		}
		$ticket_crud->grid->addPaginator(30);

		// form submittion
		$form->addSubmit('Submit');
		if($form->isSubmitted()){
			$form->js(null,$ticket_crud->js()->reload(
						[
							'event_day'=>$form['event_day'],
							'event_time'=>$form['event_time'],
						]
					))->execute();
		}

	}
}