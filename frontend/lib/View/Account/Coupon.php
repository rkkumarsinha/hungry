<?php

class View_Account_Coupon extends View{
	
	function init(){
		parent::init();

		if(!$this->api->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		$this->api->stickyGET('selectedmenu');
		$selected_view = $_GET['selectedmenu']?:'to be redeemed';
		$this->js(true)->_selector('.useraccount-coupon-verticaltabs[data-type="'.$selected_view.'"]')->addClass('active');
		$this_url = $this->api->url(null,['cut_object'=>$this->name]);
		
		$model = $this->add('Model_DiscountCoupon');
		$model->addCondition('user_id',$this->app->auth->model->id);
		$model->addCondition('status',$selected_view);
		$model->setOrder('id','desc');
		
		if(!$model->count()->getOne()){
			$this->add('View_Error',null,'no_record_found')->set('no record found');
			$this->template->set('coupon',"");
		}else{

			// throw new \Exception($model['id']);
			$lister = $this->add('Lister',null,'coupon',['view\account\coupon','coupon']);
			$lister->addHook('formatRow',function($l){
				$l->current_row_html['restaurant_image'] = str_replace("frontend", "", $l->model['restaurant_image']);
				
				$l->current_row_html['created_at'] = date('(D) d-M-Y',strtotime($l->model['created_at']));
				$l->current_row_html['created_time'] = date('h:i:s A',strtotime($l->model['created_at']));
				if(!$l->model['offer_id'])
					$l->current_row_html['discount_name'] = "Discount";
				else
					$l->current_row_html['discount_name'] = "Offer";
			});

			$lister->setModel($model);
		}


		$this->on('click','.useraccount-coupon-verticaltabs',function($js,$data)use($this_url){
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
		return ['view\account\coupon'];
	}

}