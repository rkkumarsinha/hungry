<?php

class Model_UserEventTicket extends SQL_Model{
	public $table = "user_event_ticket";

	function init(){
		parent::init();

		$this->hasOne('Event_Ticket','event_ticket_id');
		$this->hasOne('Invoice','invoice_id');
		$this->hasOne('User','user_id');
				
		$this->addField('ticket_booking_no')->defaultValue(strtoupper(substr(md5(rand(11111111,99999999)),8,9)));
		$this->addField('booking_name');
		$this->addField('secondary_booking_name');
		$this->addField('qty')->type('Number')->defaultValue(0);
		$this->addField('offer_percentage')->defaultValue(0);
		$this->addField('price')->type('money')->defaultValue(0); // ticket price
		$this->addField('total_amount')->defaultValue(0);
		$this->addField('offer_amount')->defaultValue(0);
		$this->addField('net_amount')->defaultValue(0);
		$this->addField('amount_paid')->type('money')->defaultValue(0);
		$this->addField('status')->enum(['paid','due','cancel','expire'])->defaultValue('due');
		$this->addField('payment_mode')->setValueList(['cash'=>'Cash','card'=>"Card",'imps'=>"IMPS",'e_wallet'=>"E Wallet",'no'=>"No"])->defaultValue('no');
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('payment_paid_on_date')->type('date');
		$this->addField('booking_date')->type('date');
		$this->addField('booking_time');
		$this->addField('narration')->type('text');
		
		$this->addField('mobile');
		$this->addField('email');

		$this->addField('is_verified')->type('boolean')->defaultValue(false);

		$this->addExpression('eventid')->set($this->refSQL('event_ticket_id')->fieldQuery('event_id'));
		$this->addExpression('eventtimeid')->set($this->refSQL('event_ticket_id')->fieldQuery('event_time_id'));
		$this->addExpression('eventdayid')->set($this->refSQL('event_ticket_id')->fieldQuery('event_day_id'));

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	//book only the ticket
	function bookTicket($user_id,$event_ticket_id,$booking_name,$qty,$offer_percentage,$ticket_price,$secondary_booking_name=null,$return_model=false,$invoice_id){
		$ticket_model = $this->add('Model_Event_Ticket')->load($event_ticket_id);
		//check qty is remaining or not
		
		if($qty > $ticket_model['remaining_ticket'])
			return array("status"=>"failed","message"=>"tickets sold out");

		if($offer_percentage != $ticket_model['offer_percentage'])
			return array("status"=>"failed","message"=>"offer mismatch, try again");

		if($ticket_price != $ticket_model['price'])
			return array("status"=>"failed","message"=>"price mismatch, try again");

		$total_amount = $ticket_model['price'] * $qty;
		$offer_amount = 0;
		if($ticket_model['offer_percentage']) //always calculate in percentage
			$offer_amount = ($total_amount * $ticket_model['offer_percentage'] )/100;
		$net_amount = $total_amount - $offer_amount;

		$user_ticket_model = $this->add('Model_UserEventTicket');
		$user_ticket_model['invoice_id'] = $invoice_id;
		$user_ticket_model['user_id'] = $user_id;
		$user_ticket_model['event_ticket_id'] = $event_ticket_id;
		$user_ticket_model['booking_name'] = $booking_name;
		$user_ticket_model['qty'] = $qty;
		$user_ticket_model['offer_percentage'] = $offer_percentage;
		$user_ticket_model['price'] = $ticket_price;
		$user_ticket_model['total_amount'] = $total_amount;
		$user_ticket_model['offer_amount'] = $offer_amount;
		$user_ticket_model['net_amount'] = $net_amount;
		$user_ticket_model['booking_date'] = $ticket_model['event_day'];
		$user_ticket_model['booking_time'] = $ticket_model['event_time'];
		$user_ticket_model['status'] = "due";
		$user_ticket_model['secondary_booking_name'] = $secondary_booking_name;
		$user_ticket_model['mobile'] = $this->app->auth->model['mobile']; 
		$user_ticket_model['email'] = $this->app->auth->model['email'];
		$user_ticket_model->save();

		if($return_model)
			return $user_ticket_model;

		return array(
				"event_ticket"=>$user_ticket_model['event_ticket'],
				"ticket_booking_no"=>$user_ticket_model['ticket_booking_no'],
				"booking_name" => $user_ticket_model['booking_name'],
				"qty"=>$user_ticket_model['qty'],
				"price"=>$user_ticket_model['price'],
				"offer_percentage"=>$user_ticket_model['offer_percentage'],
				"total_amount"=>$user_ticket_model['total_amount'],
				"offer_amount"=>$user_ticket_model['offer_amount'],
				"net_amount"=>$user_ticket_model['net_amount'],
				"booking_date" => $user_ticket_model['booking_date'],
				"booking_time" => $user_ticket_model['booking_time'],
				"payment"=>$user_ticket_model['status'],
				"status" => "success",
				"message"=>"congratulations! your ticket has been successfully booked.",
				"created_at"=>$user_ticket_model['created_at'],
				"amount_paid"=>$user_ticket_model['amount_paid']
			);
	}

	function paidBookedTicket($ticket_booking_no,$user_id,$amount_paid,$payment_mode){

		$model = $this->add('Model_UserEventTicket')
				->addCondition('user_id',$user_id)
				->addCondition('ticket_booking_no',$ticket_booking_no)
				->tryLoadAny();

		if(!$model->loaded())
			return array("status"=>"failed","message"=>"please re-confirm your ticket detail");

		if($model['status'] != "due")
			return array("status"=>"failed","message"=>"if already paid contact the organizer. your ticket number [".$model['ticket_booking_no']."] status: ".$model['status']);

		if($model['net_amount'] != $amount_paid)
			return  array("status"=>"fail","message"=>"please paid in one transaction, multi transaction are not allowed");

		//TODO Check with payment gateway cross verification
		$model['amount_paid'] = $amount_paid;
		$model['payment_mode'] = $payment_mode;
		$model['status'] = "paid";
		$model['payment_paid_on_date'] = $this->api->today;

		$model->save();

		return array("status"=>"success",'message'=>'your ticket has been paid successfully');

	}

	function send($send_email=true,$send_sms=true){
        if(!$this->loaded())
            throw new Exception("model must loaded, Discount Coupon");

        // send SMS
        if($send_sms){
            $this->sendSMS();
        }
        // send email
        if($send_email){
            $this->sendEmail();
        }

        return true;
    }

    function sendSMS(){
    	//To be checked
		$sms_template = $this->add('Model_EmailTemplate');
		$sms_template->addCondition('name',"BOOKTICKETSMS")->tryLoadAny();

		if(!$sms_template->loaded())
			throw new \Exception("something wrong, sms template may be delete");
				
		if(!trim($sms_template['body']))
			throw new \Exception("sms template body missing");

		$body = $sms_template['body'];		

		$body = str_replace("[user_name]", $this['name'], $body);
		$body = str_replace("[restaurant_name]", $this['restaurant'], $body);
		$body = str_replace("[offer_name]", $this['offer'], $body);
		$body = str_replace("[coupon]", $this['discount_coupon'], $body);
		$body = str_replace("[date]", $this['created_date'], $body);
		$body = str_replace("[discount]", $this['discount_taken'], $body);

		$outbox = $this->add('Model_Outbox');
		$sms_response = $outbox->sendSMS($this['mobile'],$body,$this->api->auth->model);
		if($sms_response != true){
			throw new \Exception($sms_response);
		}
		$outbox->createNew("Discount Coupon",$this['mobile'],"SMS",$body,"SMS","DiscountCoupon",$this->id,$this->api->auth->model);
		return true;   
    }

    function sendEmail(){

    }	

    function verify($narration=null){
    	if(!$this->loaded())
    		throw new \Exception("user event ticket must loaded", 1);
    	
    	$this['narration'] = $narration;
    	$this['is_verified'] = true;
    	$this->save();
    }

}