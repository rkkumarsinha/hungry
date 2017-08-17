<?php

 class Model_Wishlist extends SQL_Model{
 	public $table = "wishlist";

	function init(){
		parent::init();
		
		$this->hasOne('Model_User','user_id');

		$this->addField('name');
		$this->addField('event_ticket_id');
		$this->addField('event_id');
		$this->addField('qty');
		$this->addField('event_time_id');
		$this->addField('event_time');
		$this->addField('event_day_id');
		$this->addField('event_day');
		$this->addField('unit_price')->type('money');
		$this->addField('discount_voucher');
		$this->addField('discount_amount');

		$this->addField('disclaimer');
		
		$this->addField('is_wishcomplete')->type('boolean')->defaultValue(false);
		
		$this->addExpression('amount',function($m,$q){
			return $q->expr('((IFNULL([0],0) * IFNULL([1],0)) - IFNULL([2],0))',[$m->getElement('qty'),$m->getElement('unit_price'),$m->getElement('discount_amount')]);
		});		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

function addToWish($user_id,$event_ticket_id,$qty,$unit_price,$discount_voucher,$discount_amount,$wishlist_id=0,$type="add"){
		
		if(!in_array($type, ['add','update','delete'])){
			return ['status'=>'failed','message'=>'type not found'];
		}

		$wishlist_model = $this->add('Model_Wishlist');
		if($type == "delete"){
			$wishlist_model->load($wishlist_id);
			if($wishlist_model['user_id'] != $user_id)
				return ['status'=>'failed','message'=>'cannot delete, not associated with user'];
			$wishlist_model->delete();
			return ['status'=>'success','message'=>'delete successfully'];
		}

		$ticket_model = $this->add('Model_Event_Ticket')
						->addCondition('id',$event_ticket_id)
						;
		$ticket_model->tryLoadAny();
		if(!$ticket_model->loaded()){
			return [
					'status'=>"failed",
                    'message'=>'ticket not found'
                   ];
		}

		//check discount voucher is applicable or not
		$re_cal_discount_amount = 0;
		if($discount_voucher){
			if(!$ticket_model['is_voucher_applicable']){
				return [
						'status'=>'failed',
						'message'=>'this voucher ['.$discount_voucher.'] is not applicable on this ticket'
					];
			}

			$voucher = $this->add('Model_Voucher')->addCondition('name',$discount_voucher);
			$voucher->tryLoadAny();
			$voucher_status = $voucher->applyCoupon($qty,$ticket_model['price']);
			if($voucher_status['status'] == "success"){
				$re_cal_discount_amount = $voucher_status['discount_amount'];

			}else{
				return [
						'status'=>'failed',
						'message'=>'this discount voucher ['.$discount_voucher.'] is not applicable on this ticket'
					];
			}
			
			// if($re_cal_discount_amount != $discount_amount)
			// 	return [
			// 			'status'=>'failed',
			// 			'message'=>'discount amount ['.$discount_amount.'] is not applicable on this event ticket, actual discount amount = '.$re_cal_discount_amount
			// 		];
		}

		if($wishlist_id AND $type == "update"){
			$wishlist_model->load($wishlist_id);
		}

		//add - update are only on if is_wishcomplete is false
		$wishlist_model->addCondition('is_wishcomplete',0);

		$wishlist_model['name'] = $ticket_model['name'];
		$wishlist_model['user_id'] = $user_id;
		$wishlist_model['event_ticket_id'] = $ticket_model->id;
		$wishlist_model['qty'] = $qty;
		$wishlist_model['event_time_id'] = $ticket_model['event_time_id'];
		$wishlist_model['event_time'] = $ticket_model['event_time'];
		$wishlist_model['event_day_id'] = $ticket_model['event_day_id'];
		$wishlist_model['event_day'] = $ticket_model['event_day'];
		$wishlist_model['unit_price'] = $ticket_model['price'];
		$wishlist_model['disclaimer'] = $ticket_model['disclaimer'];
		$wishlist_model['discount_voucher'] = $discount_voucher;
		$wishlist_model['discount_amount'] = $re_cal_discount_amount;
		$wishlist_model['event_id'] = $ticket_model['event_id'];
		// $wishlist_model['is_wishcomplete'] = 0;
		$wishlist_model->save();

		$amount_array = $this->getAmounts();
        return ['status'=>'success','message'=>'your ticket added to cart','wishlist_id'=>$wishlist_model->id,'discount_amount'=>$re_cal_discount_amount,'net_amount'=>$amount_array['net_amount'],'fare_breakdown'=>$amount_array];
	}

	function emptyWishList($user_id){
		$this->add('Model_Wishlist')
				->addCondition('user_id',$user_id)
				->deleteAll();
	}
	
	function getNextSequence(){
		$wishlist = $this->add('Model_Wishlist');
		$max_number = 0;
		foreach ($wishlist as $c) {
			if($c['sequence'] > $max_number)
				$max_number = $c['sequence'];
		}

		return $max_number	+ 1;
	}

	function getNetAmount(){

		$amounts = $this->add('Model_Wishlist')
				->getAmounts();
		return $amounts['net_amount'];
		// $net_amount = 0;
		// foreach ($cart as $model) {
		// 	$net_amount += round(($model['unit_price'] * $model['qty']) - $model['discount_amount']);
		// }
		// return $net_amount;
	}

	function getAmounts(){
		$event_array = [];

		// $cart = $this->add('Model_Wishlist');
		$this->addCondition('user_id',$this->app->auth->model->id)
			->addCondition('is_wishcomplete',false);
		
		$cart = $this;
		$amount_array = [
				'subtotal'=>0,
				'internet_handling_fees'=>0,
				'base_amount'=>0,
				'tax_amount'=>0,
				'net_amount'=>0,
				'cgst'=>[],
				'sgst'=>[],
				'igst'=>[]
			];
		$state_model = $this->add('Model_State')->loadBy('name','Rajasthan');

		foreach ($cart as $key => $ci) {
			// echo "ci id ".$ci['event_id']."<br/>";
			// continue;
			
			if(!isset($event_array[$ci['event_id']])){
				$event_array[$ci['event_id']] = $this->add('Model_Event')->load($ci['event_id']);
			}
			$event_model = $event_array[$ci['event_id']];
			$item_amount = ($ci['qty'] * $ci['unit_price']);
			$item_half_amount = ($item_amount/2);

			$amount_array['subtotal'] += $item_amount;

			$tax_amount = 0;
			if($event_model['tax_percentage'] > 0 && $event_model['handling_charge'] > 0){

				$base_amount = $event_model['handling_charge'] * $ci['qty'];
				$amount_array['base_amount'] += $base_amount;
				// in state
				$half_percentage = ($event_model['tax_percentage'] /2);
				$half_tax_amount = round(($base_amount * $half_percentage)/100,2);
				$half_percentage_str = "".($event_model['tax_percentage'] /2);
				// $tax_amount = round(($event_model['tax_percentage'] * $item_amount)/100,2);

				if($event_model['state_id'] == $state_model->id){
					if(!isset($amount_array['sgst'][$half_percentage_str])){
						$amount_array['sgst'][$half_percentage_str] = ['on_amount'=>0,'tax_amount'=>0];
					}

					if(!isset($amount_array['cgst'][$half_percentage_str])){
						$amount_array['cgst'][$half_percentage_str] = ['on_amount'=>0,'tax_amount'=>0];
					}

					$amount_array['cgst'][$half_percentage_str]['on_amount'] += $base_amount;
					$amount_array['cgst'][$half_percentage_str]['tax_amount'] += $half_tax_amount;
					
					$amount_array['sgst'][$half_percentage_str]['on_amount'] += $base_amount;
					$amount_array['sgst'][$half_percentage_str]['tax_amount'] += $half_tax_amount;

					$tax_amount = $half_tax_amount * 2;
				}else{

					$tax_amount = round(($event_model['tax_percentage'] * $base_amount)/100,2);

					if(!isset($amount_array['igst'][$event_model['tax_percentage']])){
						$amount_array['igst'][$event_model['tax_percentage']] = ['on_amount'=>0,'tax_amount'=>0];
					}

					$amount_array['igst'][$event_model['tax_percentage']]['on_amount'] += $base_amount;
					$amount_array['igst'][$event_model['tax_percentage']]['tax_amount'] += $tax_amount;
				}


				$amount_array['tax_amount'] += $tax_amount;
			}


			$amount_array['internet_handling_fees'] = $amount_array['base_amount'] + $amount_array['tax_amount'];
			$amount_array['net_amount'] = $amount_array['subtotal'] + $amount_array['internet_handling_fees'];
		}

		// echo "<pre>";
		// print_r($amount_array);
		// echo "</pre>";
		return $amount_array;
	}

}
 