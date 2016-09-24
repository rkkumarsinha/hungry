<?php

class Model_DestinationEnquiry extends SQL_Model{
	public $table = "destination_enquiry";

	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->hasOne('User','user_id');
		$this->hasOne('Destination_Package','package_id');
		$this->hasOne('Destination_Highlight','occassion_id');

		$this->addField('name')->caption('username');
		$this->addField('adult');
		$this->addField('email');
		$this->addField('child');
		$this->addField('mobile');
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('created_time');
		$this->addField('total_budget');
		$this->addField('remark');
		$this->addField('status')->enum(['pending','approved','cancled'])->defaultValue('pending');

		$this->addField('is_send_to_owner')->type('boolean')->defaultValue(false);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}


	function sendEnquiryToHungry(){
		$body = "Name: ".$this['name']."<br/>";
		$body .= "adult: ".$this['adult']."<br/>";
		$body .= "child: ".$this['child']."<br/>";
		$body .= "email: ".$this['email']."<br/>";
		$body .= "mobile: ".$this['mobile']."<br/>";
		$body .= "destination: ".$this['destination']."<br/>";
		$body .= "package: ".$this['package']."<br/>";
		$body .= "occassion_id: ".$this['occassion']."<br/>";
		$body .= "total_budget: ".$this['total_budget']."<br/>";
		$body .= "remark: ".$this['remark']."<br/>";
		$body .= "date: ".$this['created_at']."<br/>";
		$body .= "time: ".$this['created_time']."<br/>";

		$subject = "You got an Enquiry";
		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail("hungrydunia@gmail.com",$subject,$body,$this->api->auth->model);
		$outbox->createNew("Destination Booking Enquiry","hungrydunia@gmail.com",$subject,$body,"Email","DestinationEnquiry",$this->id,$this->api->auth->model);
		return true;
	}

	function sendRequestToBook($send_email=true,$send_sms=true){
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

	function sendSMS(){
		
		$sms_template = $this->add('Model_EmailTemplate')->addCondition('name',"REQUESTTOBOOKSMS")->tryLoadAny();
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