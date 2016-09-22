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
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('bookedticket',"");
		}
		
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);

		$this->app->stickyGET('booked_ticket_id');
		$vp = $this->add('VirtualPage')
				->set(function($page)use($this_url){

					$form = $page->add('Form');
					$form->addField('text','narration')->validateNotNull();
					$form->addSubmit("Verify");

					if($form->isSubmitted()){
						$page->add('Model_UserEventTicket')
								->load($_GET['booked_ticket_id'])->verify($form['narration']);
						$js = [
								$form->js()->closest('.dialog')->dialog('close'),
								$this->js()->reload(null,null,$this_url)
							];
						$form->js(null,$js)->univ()->successMessage("Verify Successfully")->execute();
					}
		});
		
		$model = $this->add('Model_UserEventTicket');
		$model->addExpression('event_image')->set($model->refSQL('event_ticket_id')->fieldQuery('event_image'));
		$model->addExpression('event_name')->set($model->refSQL('event_ticket_id')->fieldQuery('event_name'));
		$model->addExpression('event_id')->set($model->refSQL('event_ticket_id')->fieldQuery('event_id'));
		// $model->addCondition('event_id',$event_model->id);
		$model->addCondition('is_verified',false);
		$model->setOrder('id','desc');

		if(!$model->count()->getOne()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('bookedticket',"");
		}

		$lister = $this->add('Lister',null,'bookedticket',['view\hostaccount\event\bookedticket','bookedticket']);
		$lister->addHook('formatRow',function($l){
			$l->current_row_html['event_image'] = str_replace("frontend", "", $l->model['event_image']);
		});

		$lister->setModel($model);


		$vp_url = $vp->getURL();
		$this->on('click','.eventactiontype',function($js,$data)use($vp_url){
			return $js->univ()->frameURL('Verify',$this->api->url($vp_url,['booked_ticket_id'=>$data['verifyticket']]));
		});

		// $this->on('click','.eventactiontype',function($js,$data)use($this_url){
		// 	$this->add('Model_UserEventTicket')->load($data['verifyticket'])->verify();
		// 	return [$this->js()->reload(null,null,$this_url)];
		// });

	}

	function render(){		
		parent::render();
	}

	function defaultTemplate(){
		return ['view\hostaccount\event\bookedticket'];
	}

}