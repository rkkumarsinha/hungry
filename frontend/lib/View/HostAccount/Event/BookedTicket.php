<?php

class View_HostAccount_Event_BookedTicket extends View{
	
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

		if($this->app->today > $event_model['closing_date']){
			$this->add('View_Error')->set('no record found');
			return;
		}

		$col = $this->add('Columns');
		$col1 = $col->addColumn(3);
		$paid_count = $this->add('Model_UserEventTicket')
					->addCondition('eventid',$event_model->id)
					->addCondition('is_verified',false)
					->addCondition('status','paid')
					->count()->getOne();
		$col1->add('View_Success')->set('Paid : '.$paid_count);
		
		$col2 = $col->addColumn(3);
		$due_count = $this->add('Model_UserEventTicket')
						->addCondition('eventid',$event_model->id)
						->addCondition('is_verified',false)
						->addCondition('status','due')
						->count()->getOne();
		$col2->add('View_Info')->set('Due : '.$due_count);

		$col3 = $col->addColumn(3);
		$cancel_count = $this->add('Model_UserEventTicket')
							->addCondition('eventid',$event_model->id)
							->addCondition('is_verified',false)
							->addCondition('status','cancel')
							->count()->getOne();
		$col3->add('View_Info')->set('Cancel : '.$cancel_count);

		$col4 = $col->addColumn(3);
		$expire_count = $this->add('Model_UserEventTicket')
					->addCondition('eventid',$event_model->id)
					->addCondition('is_verified',false)
					->addCondition('status','expire')
					->count()->getOne();
		$col4->add('View_Info')->set('Expire : '.$expire_count);

		$box = $this->add('View_Box');
		$search_form = $box->add('Form');
		$search_input = $search_form->addField('Line','search')->setAttr('placeholder','enter booking number or email or phone number');
		$search_form->addField('DropDown','status')->setValueList(['paid'=>"Paid",'due'=>"Due","cancel"=>"Cancel",'expire'=>"Expire"])->setEmptyText('Please Select');

		$search_submit = $search_form->addSubmit('Search');
		$clear_submit = $search_form->addSubmit('Clear');

		$view = $this->add('View_HostAccount_Event_PaidTicket');

		//form submit
		if($search_form->isSubmitted()){

			if($search_form->isClicked($search_submit)){
				$search_form->js(null,$view->js()->reload(['search_tag'=>$search_form['search'],'ticket_status'=>$search_form['status']]))->execute();
			}

			if($search_form->isClicked($clear_submit)){
				$search_form->js(null,$view->js()->reload(['search_tag'=>"0",'ticket_status'=>""]))->reload()->execute();
			}
		}

		// $vp = $this->add('VirtualPage')
		// 		->set(function($page)use($this_url){

		// 			$form = $page->add('Form');
		// 			$form->addField('text','narration')->validateNotNull();
		// 			$form->addSubmit("Verify");

		// 			if($form->isSubmitted()){
		// 				$page->add('Model_UserEventTicket')
		// 						->load($_GET['booked_ticket_id'])->verify($form['narration']);
		// 				$js = [
		// 						$form->js()->closest('.dialog')->dialog('close'),
		// 						$this->js()->reload(null,null,$this_url)
		// 					];
		// 				$form->js(null,$js)->univ()->successMessage("Verify Successfully")->execute();
		// 			}
		// });
		
		// $model = $this->add('Model_UserEventTicket');
		// $model->addExpression('event_image')->set($model->refSQL('event_ticket_id')->fieldQuery('event_image'));
		// $model->addExpression('event_name')->set($model->refSQL('event_ticket_id')->fieldQuery('event_name'));
		// $model->addExpression('event_id')->set($model->refSQL('event_ticket_id')->fieldQuery('event_id'));
		// $model->addCondition('event_id',$event_model->id);
		// $model->addCondition('is_verified',false);
		// if($_GET['search_tag']){
		// 	$model->addCondition('ticket_booking_no',$_GET['search_tag']);
		// }

		// $model->setOrder('id','desc');

		// if(!$model->count()->getOne()){
		// 	$this->add('View_Error',null,'no_record_found')->set('no record found');
		// 	$this->template->set('bookedticket',"");
		// }

		// $lister = $this->add('CompleteLister',null,'bookedticket',['view\hostaccount\event\bookedticket','bookedticket']);
		// $lister->addHook('formatRow',function($l){
		// 	$l->current_row_html['event_image'] = str_replace("frontend", "", $l->model['event_image']);
		// });
		// $lister->setModel($model);
  //       // $quick_search = $lister->add('QuickSearch',null,'quick_search')
		// 		    //         ->useWith($lister)
		// 		    //         ->useFields(['ticket_booking_no','booking_name','net_amount']);	

		// $paginator = $lister->add("Paginator",null,'Paginator');
  //       $paginator->setRowsPerPage(10);

		// $vp_url = $vp->getURL();
		// $this->on('click','.eventactiontype',function($js,$data)use($vp_url){
		// 	return $js->univ()->frameURL('Verify',$this->api->url($vp_url,['booked_ticket_id'=>$data['verifyticket']]));
		// });

	}

	// function render(){		
	// 	parent::render();
	// }

	// function defaultTemplate(){
	// 	return ['view\hostaccount\event\bookedticket'];
	// }

}