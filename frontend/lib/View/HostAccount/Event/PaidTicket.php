<?php

class View_HostAccount_Event_PaidTicket extends View{

	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}
		
		$event_model = $this->app->listmodel;
		if(!$event_model->loaded()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('bookedticket',"");
		}
		
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);

		$search_tag = $this->app->stickyGET('search_tag');
		$ticket_status = $this->app->stickyGET('ticket_status');
		$booked_ticket_id = $this->app->stickyGET('booked_ticket_id');

		// $this->add('View')->set($_GET['search_tag']." - ".$_GET['ticket_status']." = ".rand(19,999));
		
		$model = $this->add('Model_UserEventTicket');
		$model->addExpression('event_image')->set($model->refSQL('event_ticket_id')->fieldQuery('event_image'));
		$model->addExpression('event_name')->set($model->refSQL('event_ticket_id')->fieldQuery('event_name'));
		$model->addExpression('event_id')->set($model->refSQL('event_ticket_id')->fieldQuery('event_id'));
		$model->addCondition('event_id',$event_model->id);
		$model->addCondition('is_verified',false);
		if($ticket_status)
			$model->addCondition('status',$ticket_status);
		if($search_tag){
			$model->addCondition(
				$model->dsql()->orExpr()
					->where('ticket_booking_no',$search_tag)
					->where('email',$search_tag)
					->where('mobile',$search_tag)
				);
		}

		$model->setOrder('id','desc');

		if(!$model->count()->getOne()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('bookedticket',"");
		}

		$lister = $this->add('CompleteLister',null,'bookedticket',['view\hostaccount\event\bookedticket','bookedticket']);
		$lister->addHook('formatRow',function($l){
			$l->current_row_html['event_image'] = str_replace("frontend", "", $l->model['event_image']);
			if(!in_array($l->model['status'],['paid','due']))
				$l->current_row_html['verifyButton'] = " ";

		});
		$lister->setModel($model);
        // $quick_search = $lister->add('QuickSearch',null,'quick_search')
				    //         ->useWith($lister)
				    //         ->useFields(['ticket_booking_no','booking_name','net_amount']);	

		$paginator = $lister->add("Paginator",null,'Paginator');
        $paginator->setRowsPerPage(10);

        // if($ticket_status == "paid" OR $ticket_status == "due"){
	        $vp = $this->add('VirtualPage')
					->set(function($page)use($this_url){

						$user_ticket_model = $page->add('Model_UserEventTicket')
								->load($_GET['booked_ticket_id']);

						$form = $page->add('Form');
						if($user_ticket_model['status'] == 'due'){
							$form->addField('Number','paid_amount')->set($user_ticket_model['net_amount'])->validateNotNull();
							$form->addField('DropDown','payment_mode')->setValueList(['cash'=>'Cash','card'=>"Card",'imps'=>"IMPS",'e_wallet'=>"E Wallet"]);
						}
						$form->addField('text','narration')->validateNotNull();
						$form->addSubmit("Verify");	

						if($form->isSubmitted()){
							$paid_amount = 0;
							$payment_mode = false;
							if($user_ticket_model['status'] == 'due'){
								$paid_amount = $form['paid_amount'];
								$payment_mode = $form['payment_mode'];
							}
							
							$user_ticket_model->verify($form['narration'],$paid_amount,$payment_mode);
							$js = [
									$form->js()->closest('.dialog')->dialog('close'),
									$this->js()->reload(null,null,$this_url)
								];
							$form->js(null,$js)->univ()->successMessage("Verify Successfully")->execute();
						}
			});
			$vp_url = $vp->getURL();
			$this->on('click','.eventactiontype',function($js,$data)use($vp_url){
				return $js->univ()->frameURL('Verify',$this->api->url($vp_url,['booked_ticket_id'=>$data['verifyticket']]));
			});
        // }
	}

	function render(){		
		parent::render();
	}

	function defaultTemplate(){
		return ['view\hostaccount\event\bookedticket'];
	}

}