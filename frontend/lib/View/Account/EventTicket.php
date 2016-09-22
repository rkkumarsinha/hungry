<?php

class View_Account_EventTicket extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		$this->api->stickyGET('selectedmenu');
		$selected_view = $_GET['selectedmenu']?:'due';
		$this->js(true)->_selector('.useraccount-eventticket-verticaltabs[data-type="'.$selected_view.'"]')->addClass('active');
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);
		
		$model = $this->add('Model_UserEventTicket');
		$model->addCondition('user_id',$this->app->auth->model->id);
		$model->addCondition('status',$selected_view);
		$model->addExpression('event_image')->set($model->refSQL('event_ticket_id')->fieldQuery('event_image'));
		$model->addExpression('event_name')->set($model->refSQL('event_ticket_id')->fieldQuery('event_name'));
		$model->setOrder('id','desc');

		if(!$model->count()->getOne()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('eventticket',"");
		}else{

			// throw new \Exception($model['id']);
			$lister = $this->add('Lister',null,'eventticket',['view\account\eventticket','eventticket']);
			$lister->addHook('formatRow',function($l){
				$l->current_row_html['event_image'] = str_replace("frontend", "", $l->model['event_image']);
				
			// 	$l->current_row_html['created_at'] = date('(D) d-M-Y',strtotime($l->model['created_at']));
			// 	$l->current_row_html['created_time'] = date('h:i:s A',strtotime($l->model['created_at']));
			// 	if(!$l->model['offer_id'])
			// 		$l->current_row_html['discount_name'] = "Discount";
			// 	else
			// 		$l->current_row_html['discount_name'] = "Offer";
			});
			$lister->setModel($model);
		}


		$this->on('click','.useraccount-eventticket-verticaltabs',function($js,$data)use($this_url){
			$js = [
					$this->js()->reload(['selectedmenu'=>$data['type']],null,$this_url)
                ];
            return $js;
		});

	}

	function render(){		
		parent::render();
	}

	function defaultTemplate(){
		return ['view\account\eventticket'];
	}

}