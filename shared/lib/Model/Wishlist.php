<?php

 class Model_Wishlist extends SQL_Model{
 	public $table = "wishlist";

	function init(){
		parent::init();
		
		$this->hasOne('Model_User','user_id');

		$this->addField('name');
		$this->addField('event_ticket_id');
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
						'message'=>'this voucher ['.$discount_voucher.'] is not applicable on this ticket'
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
		// $wishlist_model['is_wishcomplete'] = 0;
		$wishlist_model->save();

        return json_encode(['status'=>"success",'message'=>'your ticket added to cart','wishlist_id'=>$wishlist_model->id]);
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

}
 