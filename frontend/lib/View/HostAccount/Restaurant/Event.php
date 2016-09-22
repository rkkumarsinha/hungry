<?php

class View_HostAccount_Restaurant_Event extends View{
	function init(){
		parent::init();

		$type = $this->app->recall('HOSTLISTTYPE');
		$rest_id = $this->app->recall('HOSTLISTID');

		if($type != "Restaurant" OR !$rest_id){
			$this->add('View_Error')->set('restaurant not found');
			return;
		}
		
		$form = $this->add('Form')->addClass('atk-box');
		$form->addField('event_name')->validateNotNull();
		$form->addSubmit('Create Event');

		$grid = $this->add('Grid',['allow_add'=>false]);
		$rest_event = $this->add('Model_EventDestinationRest')
						->addCondition('restaurant_id',$rest_id)
						->addCondition('type','event')
						;
		$grid->setModel($rest_event,['event']);
		$grid->addColumn('edit_event');
		$grid->addMethod('format_editevent',function($g,$f){
			$g->current_row_html['edit_event'] = "<a data-listid='".$g->model['event_id']."' data-listtype='Event' class='btn btn-default host-rest-event' href='#'>Edit Event</a>";
		});
		$grid->addFormatter('edit_event','editevent');

		// $this->app->stickyForget('selectedmenu');
		$url = $this->app->url('account');
		$grid->on('click','.host-rest-event',function($js,$data)use($url){
            $this->app->memorize('HOSTLISTID',$data['listid']);
            $this->app->memorize('HOSTLISTTYPE',$data['listtype']);
            return $js->univ()->location("?page=account");
        });

		if($form->isSubmitted()){
			$event = $this->add('Model_Event');
			$event['user_id'] = $this->app->auth->model->id;
			$event['name'] = $form['event_name'];
			$event->save();

			$model = $this->add('Model_EventDestinationRest');
			$model['event_id'] = $event->id;
			$model['restaurant_id'] = $rest_id;
			$model['type'] = "event";
			$model->save();

			$form->js(null,$grid->js()->reload())->univ()->successMessage("Event Create Successfully")->execute();
		}
	}
}