<?php

class Model_ReservedTable extends SQL_Model{
	public $table = "reserved_table";
	public $title_field = "booking_id";
	function init(){
		parent::init();

		$this->hasOne('Restaurant','restaurant_id');
		$this->hasOne('User','user_id');
		$this->hasOne('Discount','discount_id')->defaultValue(0);
		$this->hasOne('RestaurantOffer','restoffer_id')->defaultValue(0);

		$this->addField('booking_id')->caption('reserved id')->defaultValue(strtoupper(substr(md5(rand(11111111,99999999)),8,9)));
		$this->addField('book_table_for');//customer name
		$this->addField('no_of_adult');
		$this->addField('no_of_child');//json type 2:child,5:young
		$this->addField('email');
		$this->addField('mobile');
		$this->addField('booking_date')->type('date')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('booking_time');
		$this->addField('message')->type('text');
		$this->addField('status')->enum(['pending','confirmed','canceled'])->defaultValue('pending');
		$this->addField('total_amount')->type('money')->defaultValue(0);
		$this->addField('amount_paid')->type('money');
		$this->addField('payment_mode')->setValueList(['cash'=>'Cash','card'=>"Card",'imps'=>"IMPS",'e_wallet'=>"E Wallet"]);
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->addField('canceled_by')->enum(['host','user']);
		$this->addField('discount_offer_value')->type('text');

		$this->hasOne('CancledReason','cancled_reason_id');

		$this->addExpression('restaurant_image')->set($this->refSQL('restaurant_id')->fieldQuery('display_image'));
		$this->addExpression('restaurant_address')->set($this->refSQL('restaurant_id')->fieldQuery('address'));
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function approved(){
		if(!$this->loaded())
			throw new \Exception("table model must loaded");
		
		$this['status'] = "confirmed";
		$this->save();
		return $this;
	}

	function canceled($canceled_by,$cancled_reason_id){
		if(!$this->loaded())
		throw new \Exception("table model must loaded");
		
		$this['status'] = "canceled";
		$this['cancled_reason_id'] = $cancled_reason_id;
		$this['canceled_by'] = $canceled_by;
		$this->save();
		return $this;	
	}

	//send either email or sms
	function sendReservedTable($send_email=true,$send_sms=true){
		if(!$this->loaded())
			throw new Exception("model must loaded, Book Table");
		
		//send SMS
		if($send_sms){
			$this->sendSMS();
		}
		// send email
		if($send_email){
			$this->sendEmail();
		}

		return true;
		// //send sms
		// // $sms_str = "Dear ".$this['user'].", Greetings! ".$this['restaurant'].$this['name'];
		// // if($this['discount_id'])
		// // 	$sms_str .= "Flat ".$this['discount_id']."%";
		// // else
		// // 	$sms_str .= " Offer ".$this['offer_id'];

		// // $sms_str .= " Code ".$this['discount_coupon'];
		// // // $date = date(date('Y-m-d',$this['created_at']), strtotime("+3 days"));
		// // $date = strtotime("+3 days", strtotime($this['created_at']));
	 // //    $date = date("Y-m-d", $date);
		// // $sms_str .= " Expire on ".$date." Rate & review on www.hungrydunia.com. T&C Apply";

		// // $this->add('Controller_SMS')->sendMessage($mobile,$sms_str);

		// // $sms_str .= "{discount_code}";
		// // $body = "{discount_code}";
		// // $body = str_replace("{discount_code}", $sms_str, $sms_str);
		// $body = $sms_str;
		// // $this->add('Model_User')->send($email,"Reserved Table",$body);
		// return true;
	}

	function sendEmail(){
		$email_template = $this->add('Model_EmailTemplate')->addCondition('name',"RESERVEDTABLEEMAIL")->tryLoadAny();
		if(!$email_template->loaded()){
			throw new \Exception("email template is missing");
		}

		if(!trim($email_template['subject']))
			throw new \Exception("email template subject missing");

		if(!trim($email_template['body']))
			throw new \Exception("email template body missing");

		$subject = $email_template['subject'];
		$body = $email_template['body'];

		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail($this['email'],$subject,$body,$this->api->auth->model);
		if($email_response != true){
			throw new \Exception($email_response);
		}

		$outbox->createNew("Book Table",$this['email'],$subject,$body,"Email","ReservedTable",$this->id,$this->api->auth->model);
		return true;
	}
	
	function sendSMS(){
		
		$sms_template = $this->add('Model_EmailTemplate')->addCondition('name',"RESERVEDTABLESMS")->tryLoadAny();
		if(!$sms_template->loaded()){
			throw new \Exception("sms template is missing");
		}

		if(!trim($sms_template['body']))
			throw new \Exception("sms template body missing");

		$body = $sms_template['body'];
		// Dear [user_name], your reservation id: [booking_id] is being processed you will shortly receive confirmation email/ sms.
		
		$body = str_replace("[user_name]", $this['book_table_for'], $body);
		$body = str_replace("[booking_id]", $this['booking_id'], $body);

		$outbox = $this->add('Model_Outbox');
		$sms_response = $outbox->sendSMS($this['mobile'],$body,$this->api->auth->model);
		if($sms_response != true){
			throw new \Exception($sms_response);
		}
		$outbox->createNew("Book Table",$this['mobile']," SMS ",$body,"SMS","ReservedTable",$this->id,$this->api->auth->model);
		return true;
	}
}