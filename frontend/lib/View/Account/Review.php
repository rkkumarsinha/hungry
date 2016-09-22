<?php

class View_Account_Review extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}
		

		$this->api->stickyGET('selectedmenu');
		$selected_view = $_GET['selectedmenu']?:"approved";
		$this->js(true)->_selector('.useraccount-review-verticaltabs[data-type="'.$selected_view.'"]')->addClass('active');
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);
		
		$model = $this->add('Model_Review');
		$model->addCondition('user_id',$this->app->auth->model->id);
		if($_GET['selectedmenu'] == "pending"){
			$model->addCondition('is_approved',false);
		}else
			$model->addCondition('is_approved',true);
		$model->setOrder('id','desc');

		if(!$model->count()->getOne()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('review_lister',"");
		}else{

			// throw new \Exception($model['id']);
			$lister = $this->add('Lister',null,'review_lister',['view\account\review','review_lister']);
			$lister->addHook('formatRow',function($l){
				$l->current_row_html['profile_image_url'] = str_replace("frontend", "", $l->model['profile_image_url']);
				$l->current_row_html['created_at'] = date('(D) d-M-Y',strtotime($l->model['created_at']));
				$l->current_row_html['rating_per'] = (($l->model['rating'] / 5.0) * 100)?:0;
			});
			$lister->setModel($model);
		}

		$this->on('click','.useraccount-review-verticaltabs',function($js,$data)use($this_url){
			$js = [
					$this->js()->reload(['selectedmenu'=>$data['type']],null,$this_url)
                ];
            return $js;
		});

	}

	function defaultTemplate(){
		return ['view\account\review'];
	}

}