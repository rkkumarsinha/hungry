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
		$canclled_tab = $tab->addTab('Cancled Offer');


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
				$discount_form->js(null,$discount_tab->js()->reload())->univ()->successMessage('Discount Submitted ')->execute();
			}
		}

		$offer_form  = $offer_tab->add('Form');

		$offer_name_field = $offer_form->addField('DropDown','offer_name','Offer Category')->validateNotNull(true);
		$offer_name_field->setModel('Offer');
		$offer_name_field->setEmptyText('Please Select Offer Category');
		$offer_form->addField('line','title','Offer Name')->validateNotNull(true);
		$offer_form->addField('text','offer_detail')->validateNotNull(true);
		$offer_form->addSubmit('Send For Approved');
		
		$pending_grid = $offer_tab->add('Grid');
		$pending_grid->add('View',null,'grid_buttons')->setElement('h2')->set('Pending Offer\'s for approval');
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

			$offer_model  = $this->add('Model_Offer')->load($offer_form['offer_name']);

			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new offer";
			$notification['from_id'] = $host_restaurant->id;
			$notification['from'] = "Restaurant";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "offer";
			$notification['status'] = "pending";
			$notification['value'] = $offer_model['name']." - ".$offer_form['title'];
			$notification['message'] = $offer_form['offer_detail'];
			$notification->save();

			$new_offer = $this->add('Model_RestaurantOffer')
					->addCondition('restaurant_id',$host_restaurant->id)
					->addCondition('offer_id',$offer_form['offer_detail'])
					->addCondition('sub_name',$offer_form['title'])
					;
			$new_offer->tryLoadAny();

			$new_offer['detail'] = $offer_form['offer_detail'];
			$new_offer['is_active'] = false;
			$new_offer->save();

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
										->setOrder('created_at','desc'),
										['name','message','request_for','value','created_at']
									);

		$canclled_tab->add('Grid')->setModel(
								$this->add('Model_Notification')
								->addCondition('from_id',$host_restaurant->id)
								->addCondition('status','cancled')
								->addCondition('from','Restaurant')
								->addCondition('request_for','offer')
								->setOrder('created_at','desc'),
								['name','message','request_for','value','created_at']
							);
		
	}
}