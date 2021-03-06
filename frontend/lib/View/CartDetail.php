<?php

class View_CartDetail extends CompleteLister{
	function init(){
		parent::init();
		
		$this->addClass('hungry-cart-detail');
		$this->js('reload')->reload();

		$this->on('click','.hungry-remove-event-cart-item')->univ()->confirm('Are you sure?')
			->ajaxec(array(
            	$this->app->url(),
            	['remove_cart_item_id'=>$this->js()->_selectorThis()->data('cartid')]
        ));

		$remove_cart_item_id = $this->app->stickyGET('remove_cart_item_id');
		
		if($remove_cart_item_id){
			$this->add('Model_Cart')->load($remove_cart_item_id)->delete();
			$js_event = [
				// $this->js()->_selector('.')->html($count),
				$this->js()->closest('.hungry-event-ticket')->hide(),
				$this->js()->univ()->successMessage('removed successfully'),
				$this->js()->_selector('.hungry-cart-detail')->trigger('reload')
			];
			$this->js(null,$js_event)->execute();
		}

		$cart = $this->add('Model_Cart');
		$amount = $cart->getAmounts();
		// echo "<pre>";
		// print_r($amount);
		// echo "</pre>";
		// die();

		$this->template->trySet('sub_total', $amount['subtotal']);
		$this->template->trySet('internet_handling_fees', $amount['internet_handling_fees']);
		// $this->template->trySet('gst_charge', $amount['tax_amount']);
		$this->template->trySet('net_amount', $amount['net_amount']);

		$tdh = '<div class="row text-right" style="border:1px solid #f3f3f3;">';
		$tdh .= '<div class="col-md-6 col-sm-12 col-xs-12 col-lg-6">Base Amount </div><div class="col-md-6 col-sm-12 col-xs-12 col-lg-6"> '.$amount['base_amount'].'&nbsp;<i class="fa fa-rupee"></i></div>';
		foreach ($amount as $sub_tax_name => $tax_detail) {
			if(!is_array($tax_detail) || !count($tax_detail)) continue;

			foreach ($tax_detail as $tax_per => $detail) {
				$tdh .= '<div class="col-md-6 col-sm-12 col-xs-12 col-lg-6">'.$sub_tax_name." ".$tax_per.'% on '.$detail['on_amount'].'</div><div class="col-md-6 col-sm-12 col-xs-12 col-lg-6"> '.$detail['tax_amount'].'&nbsp;<i class="fa fa-rupee"></i></div>';
			}
		}
		$tdh .= "</div>";
		$this->template->trySetHtml('tax_detail',$tdh);

	}
	
	function setModel($m){
		parent::setModel($m);
		
		if(!$m->count()){
			$this->template->tryDel('checkout_button');

			$this->add('View_Warning')->set('your cart is empty');
			$this->add('Button')->set('Continue Ticket Booking')->js('click')->redirect($this->app->url('eventlist'));

		}else{

			$btn = $this->add('Button',null,'empty_cart')->setIcon('trash')
					->set('Remove All From Your Cart')->addClass('atk-swatch-red');
			if($btn->isClicked()){
				$this->add('Model_Cart')->emptyCart();
				$this->js(null,$this->js()->univ()->reload())->univ()->successMessage('cart empty successfully')->execute();
			}
		}
	}

	function formatRow(){
		$m = $this->model;
		
		if(!$m['event_ticket_id']){
			parent::setModel($m);
			return;
		}

		$event_ticket_model = $this->add('Model_Event_Ticket')->tryLoad($m['event_ticket_id']);
		$event_ticket_model->addExpression('event_day_id')->set(function($m,$q){
			return $m->refSQL('event_time_id')->fieldQuery('event_day_id');
		});

		if($event_ticket_model->loaded()){
			$f = $this->add('filestore/Model_File')->tryLoad($event_ticket_model['display_image_id']);
			$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
		}

		$form = $this->add('Form',null,"addtocartform",array('form/stacked'));
		$form->setLayout('form\eventaddtocartdetail');

		//cart id
		$form->addField('Hidden','cartid')->set($m->id);
		// price
		$price_field = $form->addField('Readonly','price')->set($event_ticket_model['price']);
		//quantity field
		// $qty_field = $form->addField('Number','quantity')->validateNotNull(true)->set(1)->addClass('hungrySpinner');
		// $qty_field->js(true)->spinner(array('min'=>1,'max'=>$event_ticket_model['remaining_ticket'],"step"=>1));
		// $qty_field->set($m['qty']);
		$ticket_list = [];
			
		for ($i=1; $i < $event_ticket_model['remaining_ticket']; $i++) {
			$ticket_list[$i] = $i;
		}

		$qty_field = $form->addField('Dropdown','quantity');
		$qty_field->setValueList($ticket_list);
		$qty_field->set($m['qty']);
		
		$amount_field = $form->addField('hidden','amount_hidden')->set($m['price']);
	
		$voucher_field = $form->addField('line','discount_voucher');
		$voucher_field->set($m['discount_voucher']);

		$voucher_amount_field = $form->addField('Readonly','discount_amount');
		$voucher_amount_field->set($m['discount_amount']);

		$voucher_amount_field = $form->addField('hidden','discount_amount_hidden')->set($m['discount_amount']);

		// $voucher_field->on('change',function($js,$data)use($form){
			
		// 	return $js->univ()->alert('hello');
		// 	$this->app->memorize('call_from','change');
		// 	return $form->js()->submit();
  //       });

		// amount field
		$amount_field = $form->addField('Readonly','amount')->set(($m['qty'] * $event_ticket_model['price'])-$m['discount_amount']);

		$form->layout->add('View',null,'submit_button')->addClass('hungry-remove-event-cart-item btn btn-danger')->set('x')->setAttr('title','remove ticket')->setAttr('data-cartid',$m->id)->setStyle('margin-top','20px');
		// on chnage on quantity field form submit
		$qty_field->js('change',$form->js()->submit());

		$voucher_field->js('change',$form->js()->submit());

		if($form->isSubmitted()){

			if(!$form['quantity'])
				$form->error('quantity','zero not applicable');

			//check for max number to qty
			if($form['quantity'] > $event_ticket_model['remaining_ticket'])
				$form->error('quantity','cannot greater then '.$event_ticket_model['remaining_ticket']);
			

			// check for the discount voucher is apply or not
			$discount_amount = 0;
			$discount_voucher = $form['discount_voucher'];

			
			if(trim($form['discount_voucher'])){

				if(!$event_ticket_model['is_voucher_applicable'])
					$form->displayError('discount_voucher','not applicable');

				$total_amount = $form['quantity'] * $event_ticket_model['price'];

				$voucher_model = $this->add('Model_Voucher')
								->addCondition('event_id',$event_ticket_model['event_id'])
								->addCondition('name',$form['discount_voucher'])
								->tryLoadAny()
								;
										
				if(!$voucher_model->loaded())
					$form->displayError('discount_voucher','voucher not exist');
					
				$result = $voucher_model->applyCoupon($form['quantity'],$event_ticket_model['price']);
				
				if($result['status'] == "failed")
					$form->displayError('discount_voucher',$result['message']);
					
				if($result['status'] == "success")
					$discount_amount = $result['discount_amount'];

			}


			$old_cart = $this->add('Model_Cart')->load($form['cartid']);

			$new_cart = $this->add('Model_Cart');
			$new_cart['event_ticket_id'] = $event_ticket_model['id'];
			$new_cart['name'] = $event_ticket_model['name'];
			$new_cart['qty'] = $form['quantity'];
			$new_cart['event_time_id'] = $event_ticket_model['event_time_id'];
			$new_cart['event_time'] = $event_ticket_model['event_time'];
			$new_cart['event_day_id'] = $event_ticket_model['event_day_id'];
			$new_cart['event_day'] = $event_ticket_model['event_day'];
			$new_cart['unit_price'] = $event_ticket_model['price'];
			$new_cart['disclaimer'] = $event_ticket_model['disclaimer'];
			$new_cart['sequence'] = $old_cart['sequence'];
			$new_cart['discount_amount'] = $discount_amount;
			$new_cart['discount_voucher'] = $discount_voucher;
			$new_cart['event_id'] = $event_ticket_model['event_id'];
			$new_cart->save();

			//delete old cart and adding new cart item
			$old_cart->delete();
			$form->js(null,$this->js()->reload())->execute();
			// $form->js()->redirect($this->api->url())->execute();
		}

		$this->current_row_html['addtocartform'] = $form->getHtml();
		// add display
		$this->current_row_html['detail'] = $event_ticket_model['detail'];
		//add first image
		$this->current_row_html['first_image'] = $path;
		$this->current_row_html['id'] = $this->model->id;
		
		$this->current_row_html['event_day'] = date('(D) d - M - Y',strtotime($m['event_day']));
		$this->current_row_html['event_name'] = $event_ticket_model['event'];
		$this->current_row_html['disclaimer'] = $event_ticket_model['disclaimer'];
		$this->current_row_html['sequence'] = $m['sequence'];

		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/cartdetail'];
	}

}