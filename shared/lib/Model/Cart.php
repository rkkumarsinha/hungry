<?php

 class Model_Cart extends \Model{

	function init(){
		parent::init();

		$this->setSource('Session');
		
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
		$this->addField('sequence')->type('Number')->defaultValue(0);
	}

	function addTicket($event_ticket_id,$event_ticket,$qty,$event_time_id, $event_time,$event_day_id,$event_day,$unit_price,$disclaimer,$discount_voucher=null,$discount_amount=0,$event_id){
		$this->unload();

		if(!is_numeric($qty)) $qty=1;

		if(!is_numeric($event_ticket_id)) return;

		$re_cal_discount_amount = 0;
		if($discount_voucher){
			$voucher_model = $this->add('Model_Voucher')
							->addCondition('event_id',$event_id)
							->addCondition('name',$discount_voucher)
							->tryLoadAny()
							;
											
			if(!$voucher_model->loaded())
				throw new \Exception("discount voucher not found", 1);
				
			
			$result = $voucher_model->applyCoupon($qty,$unit_price);
			
			if($result['status'] == "success"){
				$re_cal_discount_amount = $result['discount_amount'];
			}
		}
				
		if($re_cal_discount_amount != $discount_amount)
			throw new \Exception("some thing happen wrong", 1);
			
		
		$this['event_ticket_id'] = $event_ticket_id;
		$this['name'] = $event_ticket;
		$this['qty'] = $qty;
		$this['event_time_id'] = $event_time_id;
		$this['event_time'] = $event_time;
		$this['event_day_id'] = $event_day_id;
		$this['event_day'] = $event_day;
		$this['unit_price'] = $unit_price;
		$this['disclaimer'] = $disclaimer;
		$this['sequence'] = $this->getNextSequence();
		$this['discount_voucher'] = $discount_voucher;
		$this['discount_amount'] = $re_cal_discount_amount;

		$this->save();
	}

	function getNextSequence(){
		$cart = $this->add('Model_Cart');
		$max_number = 0;
		foreach ($cart as $c) {
			if($c['sequence'] > $max_number)
				$max_number = $c['sequence'];
		}

		return $max_number	+ 1;
	}

	function getEventCount(){
		$cart = $this->add('Model_Cart');
		$count = 0;
		foreach ($cart as $cart_item) {
			$count ++;
		}

		return $count;
	}

	function emptyCart(){
		$this->add('Model_Cart')->deleteAll();
	}

	function updateItem($event_ticket_id,$event_ticket_name,$event_time_id,$event_time,$event_day_id,$event_day,$unit_price,$new_qty,$disclaimer,$discount_voucher=null,$discount_amount=0,$event_id){
		if(!$this->loaded())
			throw new \Exception("cart model must loaded");
		

		$re_cal_discount_amount = 0;
		if($discount_voucher){
			$voucher_model = $this->add('Model_Voucher')
							->addCondition('event_id',$event_id)
							->addCondition('name',$discount_voucher)
							->tryLoadAny()
							;
											
			if(!$voucher_model->loaded())
				throw new \Exception("discount voucher not found", 1);
				
			
			$result = $voucher_model->applyCoupon($qty,$unit_price);
			
			if($result['status'] == "success"){
				$re_cal_discount_amount = ($qty*$unit_price) - $result['discount_amount'];
			}
		}

		if($re_cal_discount_amount != $discount_amount)
			throw new \Exception("some thing happen wrong", 1);


		$this['event_ticket_id'] = $event_ticket_id;
		$this['name'] = $event_ticket_name;
		$this['qty'] = $new_qty;
		$this['event_time_id'] = $event_time_id;
		$this['event_time'] = $event_time;
		$this['event_day_id'] = $event_day_id;
		$this['event_day'] = $event_day;
		$this['unit_price'] = $unit_price;
		$this['disclaimer'] = $disclaimer;
		$this['discount_voucher'] = $discount_voucher;
		$this['discount_amount'] = $re_cal_discount_amount;
		$this->save();
	}

	function getNetAmount(){
		$cart = $this->add('Model_Cart');
		$net_amount = 0;
		foreach ($cart as $model) {
			$net_amount += round(($model['unit_price'] * $model['qty']) - $model['discount_amount']);
		}
		return $net_amount;
	}

}
 