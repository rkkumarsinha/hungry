<?php

class View_EventTicket extends View{
	public $option = ['show_image'=>true];
	function setModel($m){
		parent::setModel($m);

		$cart = $this->add('Model_Cart');
		$count = $cart->getEventCount();
		$cart_view = $this->add('View',null,null,['view/addtocart']);
		$cart_view->js('reload')->reload();
		$cart_view->template->set('event_count',$count);

		$event_day_model = $this->add('Model_Event_Day')
							->addCondition('event_id',$m->id)
							->setOrder('on_date','asc')
							->getRows();
		
		$day_tabs = $this->add('Tabs',null,null,['view/hungryeventtabs']);
		foreach ($event_day_model as $day_model) {
			$day_tab = $day_tabs->addTab($day_model['name']);

			$event_time_model = $this->add('Model_Event_Time')
								->addCondition('event_id',$m->id)
								->addCondition('event_day_id',$day_model['id'])
								->getRows()
								;
			$time_tabs = $day_tab->add('Tabs',null,null,['view/hungryeventtabs']);

			if(!count($event_time_model)){
				$day_tab->add('View_Info')->set('no record found');
			}

			foreach ($event_time_model as $time_model) {
				$time_tab = $time_tabs->addTab($time_model['name']);
				
				$event_ticket_model = $this->add('Model_Event_Ticket')
								->addCondition('event_id',$m->id)
								->addCondition('event_time_id',$time_model['id'])
								;
				if($event_ticket_model->count()->getOne()){
					foreach ($event_ticket_model as $ticket_model) {
						$time_tab->add('View_AddToCartButton',['option'=>$this->option])->setModel($ticket_model);
					}
				}else
					$time_tab->add('View_Info')->set('no record found');
			}

		}


		// $event_day_model = $this->add('Model_Event_Day')->addCondition('event_id',$m->id);

		// $day_lister = $this->add('CompleteLister',null,'event_day_lister',['view/eventaddtocartbutton','event_day_lister']);
		// $day_lister->setModel($event_day_model);

		// if($_GET['event_day_id']){
		// 	$event_day_id = $_GET['event_day_id'];
		// }else{
		// 	//load first event day id
		// 	$first_event_day_model = $this->add('Model_Event_Day')->addCondition('event_id',$m->id);
		// 	$event_day_id = $first_event_day_model->setLimit(1)->tryLoadAny()->id;
		// }

		// //Event Time Lister
		// $event_time_model = $this->add('Model_Event_Time')
		// 					->addCondition('event_id',$m->id)
		// 					->addCondition('event_day_id',$event_day_id);
		// $time_lister = $this->add('CompleteLister',null,'event_time_lister',['view/eventaddtocartbutton','event_time_lister']);
		// $time_lister->setModel($event_time_model);

		// //Event Ticket
		// if($_GET['event_time_id']){
		// 	$event_time_id = $_GET['event_time_id'];
		// }else{
		// 	//load first event day id
		// 	$first_event_time_model = $this->add('Model_Event_Time')
		// 							->addCondition('event_id',$m->id)
		// 							->addCondition('event_day_id',$event_day_id)
		// 							;
		// 	$event_time_id = $first_event_time_model->setLimit(1)->tryLoadAny()->id;
		// }		
		// $ticket_model = $this->add('Model_Event_Ticket')
		// 				->addCondition('event_id',$m->id)
		// 				->addCondition('event_time_id',$event_time_id)
		// 				;
		// $ticket_lister = $this->add('CompleteLister',null,'event_ticket_lister',['view/eventaddtocartbutton','event_ticket_lister']);
		// $ticket_lister->setModel($ticket_model);

		// $time_lister_url = $this->app->url(null,['cut_object'=>$time_lister->name]);

		// $this->on('click','.hungry-event-day',function($js,$data)use($time_lister,$time_lister_url){
		// 	// return $js->alert($data['eventdayid']);
		// 	return $time_lister->js()->reload(['event_day_id'=>$data['eventdayid']],null,$time_lister_url);
		// });

	}

	// function DefaultTemplate(){
	// 	return ['view/eventaddtocartbutton'];
	// }

}