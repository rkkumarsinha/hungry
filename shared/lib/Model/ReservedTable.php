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
		$this->addField('status')->enum(['pending','confirmed','canceled','verified'])->defaultValue('pending');
		$this->addField('total_amount')->type('money')->defaultValue(0);
		$this->addField('discount_taken')->type('money');
		$this->addField('amount_paid')->type('money');
		$this->addField('payment_mode')->setValueList(['cash'=>'Cash','card'=>"Card",'imps'=>"IMPS",'e_wallet'=>"E Wallet"]);
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->addField('canceled_by')->enum(['host','user']);
		$this->addField('discount_offer_value')->type('text');

		$this->hasOne('CancledReason','cancled_reason_id');

		$this->addExpression('restaurant_image')->set($this->refSQL('restaurant_id')->fieldQuery('display_image'));
		$this->addExpression('restaurant_address')->set($this->refSQL('restaurant_id')->fieldQuery('address'));
		$this->addExpression('restaurant_host_user')->set($this->refSQL('restaurant_id')->fieldQuery('user_id'));
		// $this->add('dynamic_model/Controller_AutoCreator');
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
			throw new \Exception("reserve table model must loaded");
		
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

		$reservation_date = date("D, d M Y", strtotime($this['booking_date']));

		$body = str_replace("{booking_id}", $this['booking_id'], $body);
		$body = str_replace("{restaurant}", $this['restaurant'], $body);
		$body = str_replace("{reservation_date}", $reservation_date, $body);
		$body = str_replace("{reservation_time}", $this['booking_time'], $body);
		$body = str_replace("{discount_offer}", $this['discount_offer_value'], $body);
		$body = str_replace("{adult}", $this['no_of_adult']." Adult ", $body);
		$body = str_replace("{child}", $this['no_of_child']." Child ", $body);
		$body = str_replace("{name}", $this['book_table_for'], $body);
		$body = str_replace("{special_request}", $this['message'], $body);
		$body = str_replace("{restaurant_detail}", $this['restaurant_address'], $body);


		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail($this['email'],$subject,$body,$this->api->auth->model);
		if($email_response != true){
			throw new \Exception($email_response);
		}
		$outbox->createNew("Book Table",$this['email'],$subject,$body,"Email","ReservedTable",$this->id,$this->api->auth->model);

		return true;
	}
	
	function sendEnquiryEmailToHost(){

		//check for host has a email id not
		if(!$this['restaurant_host_user']) return;

		$host = $this->add('Model_User')->addCondition('type','host');
		$host->addCondition('id',$this['restaurant_host_user']);
		$host->tryLoadAny();
		if(!$host->loaded()) return;
			
		$email_template = $this->add('Model_EmailTemplate');
		$email_template->addCondition('name',"HOSTRESERVEDTABLEENQUIRYEMAIL");
		$email_template->tryLoadAny();
		if(!$email_template->loaded()){
			throw new \Exception("email template is missing");
		}

		if(!trim($email_template['subject']))
			throw new \Exception("email template subject missing");

		if(!trim($email_template['body']))
			throw new \Exception("email template body missing");
		
		$subject = $email_template['subject'];
		$body = $email_template['body'];

		$reservation_date = date("D, d M Y", strtotime($this['booking_date']));

		$body = str_replace("{booking_id}", $this['booking_id'], $body);
		$body = str_replace("{restaurant}", $this['restaurant'], $body);
		$body = str_replace("{reservation_date}", $reservation_date, $body);
		$body = str_replace("{reservation_time}", $this['booking_time'], $body);
		$body = str_replace("{discount_offer}", $this['discount_offer_value'], $body);
		$body = str_replace("{adult}", $this['no_of_adult']." Adult ", $body);
		$body = str_replace("{child}", $this['no_of_child']." Child ", $body);
		$body = str_replace("{name}", $this['book_table_for'], $body);
		$body = str_replace("{special_request}", $this['message'], $body);
		$body = str_replace("{restaurant_detail}", $this['restaurant_address'], $body);

		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail($host['email'],$subject,$body,$host);
		if($email_response != true){
			throw new \Exception($email_response);
		}
		$outbox->createNew("Book Table Enquiry to Host",$host['email'],$subject,$body,"Email","ReservedTable",$this->id,$host);
	}

	function sendProcessingSMS(){

		$sms_template = $this->add('Model_EmailTemplate')->addCondition('name',"RESERVEDTABLESMS")->tryLoadAny();
		if(!$sms_template->loaded()){
			throw new \Exception("sms template is missing");
		}

		if(!trim($sms_template['body']))
			throw new \Exception("sms template body missing");

		$body = $sms_template['body'];
		// Dear [user_name], your reservation id: [booking_id] is being processed you will shortly receive confirmation email/ sms.
		
		$body = str_replace("{user_name}", $this['book_table_for'], $body);
		$body = str_replace("{booking_id}", $this['booking_id'], $body);
		
		$outbox = $this->add('Model_Outbox');
		$sms_response = $outbox->sendSMS($this['mobile'],$body,$this->api->auth->model);
		if($sms_response != true){
			throw new \Exception($sms_response);
		}
		$outbox->createNew("Book Table",$this['mobile']," SMS ",$body,"SMS","ReservedTable",$this->id,$this->api->auth->model);
		return true;
	}

	function sendSMS(){
		
		$sms_template = $this->add('Model_EmailTemplate')->addCondition('name',"TABLECONFIRMATIONSMS")->tryLoadAny();
		if(!$sms_template->loaded()){
			throw new \Exception("sms template is missing");
		}

		if(!trim($sms_template['body']))
			throw new \Exception("sms template body missing");

		$body = $sms_template['body'];
		//Dear {name}, Greetings! your booking request conformed at {restaurant} on {day},{date} at {hh;mm-am/pm} for {A}A+{C}C Pax , booking id {id} {REQUEST}. offers {DIS/OFF} code {}. Rate & Review us on www.hungrydunia.com.T&C Apply
		
		$body = str_replace("{name}", $this['book_table_for'], $body);
		$body = str_replace("{restaurant}", $this['restaurant'], $body);
		$body = str_replace("{day}", date("D",strtotime($this['booking_date'])), $body);
		$body = str_replace("{date}", date("d-M-Y",strtotime($this['booking_date'])), $body);
		$body = str_replace("{hh;mm-am/pm}",date('h:i a',strtotime($this['booking_time'])), $body);
		$body = str_replace("{A}", $this['no_of_adult'], $body);
		$body = str_replace("{C}", $this['no_of_child'], $body);
		$body = str_replace("{id}", $this['booking_id'], $body);
		$body = str_replace("{REQUEST}", $this['message'], $body);

		$body = str_replace("{DIS/OFF}", $this['discount_offer_value'], $body);
		$body = str_replace("{}", "", $body);
		
		$outbox = $this->add('Model_Outbox');
		$sms_response = $outbox->sendSMS($this['mobile'],$body,$this->api->auth->model);
		if($sms_response != true){
			throw new \Exception($sms_response);
		}
		$outbox->createNew("Book Table",$this['mobile']," SMS ",$body,"SMS","ReservedTable",$this->id,$this->api->auth->model);
		return true;
	}
}