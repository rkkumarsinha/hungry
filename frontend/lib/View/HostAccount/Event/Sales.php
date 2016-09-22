<?php

class View_HostAccount_Event_Sales extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");
		
		$event_model = $this->app->listmodel;
		
		if(!$event_model->loaded()){
			$this->add('View_Error')->set('no record found');
			return;
		}

		// Ticket
		$form = $this->add('Form',null,null,['form/empty'])->addClass('atk-box');
		$col = $form->add('Columns');
		$col1 = $col->addColumn(4);
		$col2 = $col->addColumn(4);
		$col3 = $col->addColumn(4);

		$col1->add('View')->set("Event Day");
		$field_event_day = $col1->addField('DropDown','event_day');
		$day_model = $this->add('Model_Event_Day')->addCondition('event_id',$event_model->id);
		$field_event_day->setModel($day_model);
		$field_event_day->setEmptyText('Please Select Day');

		$col2->add('View')->set("Event Time");
		$field_event_time = $col2->addField('DropDown','event_time');
		$time_model = $this->add('Model_Event_Time')->addCondition('event_id',$event_model->id);
		if($_GET['form_event_day'])
			$time_model->addCondition('event_day_id',$_GET['form_event_day']);
		$field_event_time->setModel($time_model);
		$field_event_time->setEmptyText('Please Select Time');

		$col3->add('View')->set("Event Ticket");
		$field_event_ticket = $col3->addField('DropDown','event_ticket');
		$ticket_model = $this->add('Model_Event_Ticket')->addCondition('event_id',$event_model->id);
		if($_GET['form_event_time'])
			$ticket_model->addCondition('event_time_id',$_GET['form_event_time']);

		$field_event_ticket->setModel($ticket_model);
		$field_event_ticket->setEmptyText('Please Select Ticket');

		// day reload pe time reload
		$field_event_day->js('change',
							$field_event_time->js()->reload(
								null,null,[
										$this->app->url(null,['cut_object'=>$field_event_time->name]),'form_event_day'=>$field_event_day->js()->val()
									]));

		// time reload pe time ticket
		$field_event_time->js('change',
							$field_event_ticket->js()->reload(
								null,null,[
										$this->app->url(null,['cut_object'=>$field_event_ticket->name]),'form_event_time'=>$field_event_time->js()->val()
									]));

		$view = $this->add('View');

		$total_model = $view->add('Model_UserEventTicket')
				->addCondition('eventid',$event_model->id)
				->addCondition('status','paid');
		if($_GET['event_day']){
			$total_model->addCondition('eventdayid',$_GET['event_day']);
		}
		if($_GET['event_time']){
			$total_model->addCondition('eventtimeid',$_GET['event_time']);
		}
		if($_GET['event_ticket']){
			$total_model->addCondition('event_ticket_id',$_GET['event_ticket']);
		}
		$total_amount_sum = $total_model->sum('net_amount');

		$view->add('View_Info')->setHtml("Total Amount: ".$total_amount_sum."&nbsp;<li class='fa fa-rupee'></li>");

		$tabs = $view->add('Tabs');
		$up_show_tab = $tabs->addTab('Paid and Up Show')->setStyle('overflow','auto');
		$down_show_tab = $tabs->addTab('Paid and Down Show')->setStyle('overflow','auto');
		

		$up_show_model = $up_show_tab->add('Model_UserEventTicket')
				->addCondition('eventid',$event_model->id)
				->addCondition('status','paid')
				->addCondition('is_verified',true)
				->setOrder('payment_paid_on_date','desc')
				;

		if($_GET['event_day']){
			$up_show_model->addCondition('eventdayid',$_GET['event_day']);
		}
		if($_GET['event_time']){
			$up_show_model->addCondition('eventtimeid',$_GET['event_time']);
		}
		if($_GET['event_ticket']){
			$up_show_model->addCondition('event_ticket_id',$_GET['event_ticket']);
		}

		$up_grid = $up_show_tab->add('Grid');
		$up_grid->addSno();
		$up_grid->setModel($up_show_model,['ticket_booking_no','qty','total_amount','offer_percentage','net_amount','amount_paid','payment_mode']);
		$up_grid->addPaginator(20);
	
		$down_show_model = $down_show_tab->add('Model_UserEventTicket')
				->addCondition('eventid',$event_model->id)
				->addCondition('status','paid')
				->addCondition('is_verified',false)
				->setOrder('payment_paid_on_date','desc')
				;
		if($_GET['event_day']){
			$down_show_model->addCondition('eventdayid',$_GET['event_day']);
		}
		if($_GET['event_time']){
			$down_show_model->addCondition('eventtimeid',$_GET['event_time']);
		}
		if($_GET['event_ticket']){
			$down_show_model->addCondition('event_ticket_id',$_GET['event_ticket']);
		}

		$down_grid = $down_show_tab->add('Grid');
		$down_grid->addSno();
		$down_grid->setModel($down_show_model,['ticket_booking_no','qty','total_amount','offer_percentage','net_amount','amount_paid','payment_mode']);
		$down_grid->addPaginator(20);

		//form submittion
		$form->addSubmit('Submit');
		if($form->isSubmitted()){

			$form->js(null,$view->js()->reload(
						[
							'event_day'=>$form['event_day'],
							'event_time'=>$form['event_time'],
							'event_ticket'=>$form['event_ticket'],
						]
					))->execute();
		}

	}
}