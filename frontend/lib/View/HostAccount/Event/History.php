<?php

class View_HostAccount_Event_History extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$event_model = $this->app->listmodel;
			

		$tabs = $this->add('Tabs');
		$paid_tab = $tabs->addTab('Paid')->setStyle('overflow','auto');
		$due_tab = $tabs->addTab('Due')->setStyle('overflow','auto');
		// $cancel_tab = $tabs->addTab('Cancel')->setStyle('overflow','auto');
		$expire_tab = $tabs->addTab('Expire')->setStyle('overflow','auto');
	
		// Paid
		$paid_model = $paid_tab->add('Model_UserEventTicket');
		$paid_model->addCondition('eventid',$event_model->id);
		$paid_model->addCondition('status','paid');
		$paid_model->setOrder('created_at','desc');

		$paid_grid = $paid_tab->add('Grid');
		$paid_grid->setModel($paid_model);
		$paid_grid->addPaginator($ipp=50);

		// Due
		$due_model = $due_tab->add('Model_UserEventTicket');
		$due_model->addCondition('eventid',$event_model->id);
		$due_model->addCondition('status','due');
		$due_model->setOrder('created_at','desc');
		
		$due_grid = $due_tab->add('Grid');
		$due_grid->setModel($due_model);
		$due_grid->addPaginator($ipp=50);

		// cancel
		// $cancel_model = $cancel_tab->add('Model_UserEventTicket');
		// $cancel_model->addCondition('eventid',$event_model->id);
		// $cancel_model->addCondition('status','cancel');
		// $cancel_model->setOrder('created_at','desc');
		
		// $cancel_grid = $cancel_tab->add('Grid');
		// $cancel_grid->setModel($cancel_model);
		// $cancel_grid->addPaginator($ipp=50);

		// cancel
		$expire_model = $expire_tab->add('Model_UserEventTicket');
		$expire_model->addCondition('eventid',$event_model->id);
		$expire_model->addCondition('status','expire');
		$expire_model->setOrder('created_at','desc');
		
		$expire_grid = $expire_tab->add('Grid');
		$expire_grid->setModel($expire_model);
		$expire_grid->addPaginator($ipp=50);



	}
}