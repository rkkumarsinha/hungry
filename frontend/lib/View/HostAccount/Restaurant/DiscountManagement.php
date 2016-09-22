<?php

class View_HostAccount_Restaurant_DiscountManagement extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;


		$tab = $this->add('Tabs');
		$discount_tab = $tab->addTab('Create Discount');
		$offer_tab = $tab->addTab('Create Offer');
		$approved_tab = $tab->addTab('Approved Offer');
		$canclled_tab = $tab->addTab('Canclled Offer');


		$old_request = $this->add('Model_Notification')
				->addCondition('from_id',$host_restaurant->id)
				->addCondition('status',"pending")
				->addCondition('request_for',"discount")
				;
		if($old_request->count()->getOne()){
			$old_request->tryLoadany();
			$discount_tab->add('View_Warning')->set("wait for your previous discount(".$old_request['value'].") to be approved/ cancled");

		}else{
			$discount_form = $discount_tab->add('Form');
			$discount_form->addField('Number','discount_percentage')->validateNotNull(true);
			$discount_form->addSubmit('Send For Approved');
			
			if($discount_form->isSubmitted()){

				$notification = $this->add('Model_Notification');
				$notification['name'] = "Request for new discount";
				$notification['from_id'] = $host_restaurant->id;
				$notification['from'] = "Restaurant";
				$notification['to'] = "HungryDunia";
				$notification['request_for'] = "discount";
				$notification['status'] = "pending";
				$notification['message'] = $host_restaurant['name'];
				$notification['value'] = $discount_form['discount_percentage'];
				$notification->save();
			}
		}

		$offer_form  = $offer_tab->add('Form');
		$offer_form->addField('line','offer_name')->validateNotNull(true);
		$offer_form->addField('text','offer_detail')->validateNotNull(true);
		$offer_form->addSubmit('Send For Approved');
		
		$pending_grid = $offer_tab->add('Grid');
		$pending_grid->setModel(
							$this->add('Model_Notification')
							->addCondition('from_id',$host_restaurant->id)
							->addCondition('status','pending')
							->addCondition('from','Restaurant')
							->addCondition('request_for','offer')
							->setOrder('created_at','desc')
						,['created_at','value','message']);

		$pending_grid->addPaginator($ipp=10);

		if($offer_form->isSubmitted()){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new offer";
			$notification['from_id'] = $host_restaurant->id;
			$notification['from'] = "Restaurant";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "offer";
			$notification['status'] = "pending";
			$notification['value'] = $offer_form['offer_name'];
			$notification['message'] = $offer_form['offer_detail'];
			$notification->save();
			$js_event = [
							$offer_form->js()->reload(),
							$pending_grid->js()->reload()
						];
			$offer_form->js(null,$js_event)->univ()->successMessage('send for approval')->execute();
		}


		$approved_tab->add('Grid')->setModel(
										$this->add('Model_Notification')
										->addCondition('from_id',$host_restaurant->id)
										->addCondition('status','approved')
										->addCondition('from','Restaurant')
										->addCondition('request_for','offer')
										->setOrder('created_at','desc')
									);

		$canclled_tab->add('Grid')->setModel(
								$this->add('Model_Notification')
								->addCondition('from_id',$host_restaurant->id)
								->addCondition('status','cancled')
								->addCondition('from','Restaurant')
								->addCondition('request_for','offer')
								->setOrder('created_at','desc')
							);
		
	}
}