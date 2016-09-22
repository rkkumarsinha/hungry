<?php

class View_HostAccount_Restaurant_DiscountCoupon extends CompleteLister{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;

		$dc_model = $this->add('Model_DiscountCoupon');
		$dc_model->addCondition('restaurant_id',$host_restaurant->id);
		$dc_model->addCondition('status','to be redeemed');
		$dc_model->addExpression('profile_image_url')->set($dc_model->refSQL('user_id')->fieldQuery('profile_image_url'));
		$dc_model->setOrder('created_at','desc');
		$discount_offer_voucher = $this;
		$this->api->stickyGET('coupon_id');
		$vp = $this->add('VirtualPage')
				->set(function($page)use($discount_offer_voucher){
					$this->api->stickyGET('coupon_id');
					$id = $_GET['coupon_id'];
					// $id = $_GET[$page->short_name.'_id'];
					$discount_model = $page->add('Model_DiscountCoupon')->load($id);

					// Offer Form
					if($discount_model['offer_id']){
						$page->add('View_Info')->set("Offer :".$discount_model['offer'] ." Voucher: ".$discount_model['discount_coupon']);
						$form = $page->add('Form',null,null,['form/stacked']);
						$form->addField('Number','total_amount')->validateNotNull();
						$form->addField('line','offer_name')->validateNotNull();
						$form->addField('Number','discount_amount')->validateNotNull();
						$form->addField('Number','amount_to_be_paid')->validateNotNull();
						$form->addField('DropDown','payment_mode')->setValueList(['cash'=>"Cash",'card'=>"Card",'e-wallet'=>"E-wallet"])->validateNotNull()->setEmptyText('Please Select Payment Mode');
						$form->addSubmit('Approve');
						if($form->isSubmitted()){
							$discount_model['discount_taken'] = $form['discount_amount'];
							$discount_model['status'] = 'redeemed';
							$discount_model['total_amount'] = $form['total_amount'];
							$discount_model['amount_paid'] = $form['amount_to_be_paid'];
							$discount_model['payment_mode'] = $form['payment_mode'];
							$discount_model->save();
							// Todo send email or sms
							$js = [
								$form->js()->closest('.dialog')->dialog('close'),
								$discount_offer_voucher->js()->_selector('.col-md-6[data-recordid='.$id.']')->hide()
							];
							$form->js(null,$js)->univ()->successMessage("Approved Successfully")->execute();
						}

					}else{
						$form = $page->add('Form',null,null,['form/empty']);
						$total_amount_field = $form->addField('Number','total_amount')->validateNotNull();
						$d_percentage_field = $form->addField('Number','discount_percentage')->setAttr('Disabled',true);
						$discount = str_replace("%", "", $discount_model['discount']);
						$d_percentage_field->set($discount);

						$discount_amount_field = $form->addField('Number','discount_amount');
						$form->addField('Number','amount_to_be_paid')->validateNotNull();
						$form->addField('DropDown','payment_mode')->setValueList(['cash'=>"Cash",'card'=>"Card",'e-wallet'=>"E-wallet"])->validateNotNull()->setEmptyText('Please Select Payment Mode');
						$form->addSubmit('Approve');

						$this->api->stickyGET('total_amount');
						if($_GET['total_amount']){
							$discount_amount_field->set(($_GET['total_amount'] * $discount / 100.00))->setAttr('Disabled',true);
						}

						$total_amount_field->js('change',$discount_amount_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$discount_amount_field->name]),'total_amount'=>$total_amount_field->js()->val()]));

						if($form->isSubmitted()){
							// $discout_model = $page->add('Model_DiscountCoupon')->load($id);
							$discount = str_replace("%", "", $discount_model['discount']);
							$discount_taken = $form['total_amount'] * $discount / 100.00;
							$discount_model['discount_taken'] = $discount_taken;
							$discount_model['status'] = 'redeemed';
							$discount_model['total_amount'] = $form['total_amount'];
							$discount_model['amount_paid'] = $form['total_amount'] - $discount_taken;
							$discount_model['payment_mode'] = $form['payment_mode'];
							$discount_model->save();
							// Todo send email or sms
							$js = [
								$form->js()->closest('.dialog')->dialog('close'),
								$discount_offer_voucher->js()->_selector('.col-md-6[data-recordid='.$id.']')->hide()
							];
							$form->js(null,$js)->univ()->successMessage("Approved Successfully")->execute();
						}
					}
				});

		$vp_url = $vp->getURL();
		// $this->js(true)->univ()->frameURL('MyPopup',$vp->getURL());
		$this->on('click','.verify-button',function($js,$data)use($vp_url){
			return $js->univ()->frameURL('Verify',$this->api->url($vp_url,['coupon_id'=>$data['couponid']]));
		});
		// $discount_offer_voucher->addQuickSearch(['name','email','mobile','created_at','discount_coupon']);
		$this->setModel($dc_model);

		$quick_search = $this->add('QuickSearch',null,'quick_search')
				            ->useWith($this)
				            ->useFields(['mobile','email','name','discount_coupon','created_at']);	

		$paginator = $this->add("Paginator",null,'Paginator');
        $paginator->setRowsPerPage(10);
	}

	function setModel($model){
		parent::setModel($model);

		if(!$model->count()->getOne()){
			$this->add('View_Warning',null,'no_record_found')->set('no record found');
		}else
			$this->template->tryDel('no_record_found');
	}
	function formatRow(){
		$this->current_row_html['created_at'] = date('(D) d-M-Y',strtotime($this->model['created_at']));
		$this->current_row_html['created_time'] = date('h:i:s A',strtotime($this->model['created_at']));
		if(!$this->model['offer_id'])
			$this->current_row_html['discount_name'] = "Discount";
		else
			$this->current_row_html['discount_name'] = "Offer";
				
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/hostaccount/couponlister'];
	}
}