<?php

class Model_User extends SQL_Model{
	public $table="user";
	function init(){
		parent::init();

		$this->hasOne('ReferralUser','referral_user_id')->mandatory(true);
		$this->hasOne('Country','country_id')->mandatory(true);
		$this->hasOne('State','state_id')->mandatory(true);
		$this->hasOne('City','city_id')->mandatory(true);

		$this->addField('name')->mandatory(true);
		$this->addField('password')->type('password')->mandatory(true);
		$this->addField('email')->mandatory(true);
		$this->addField('created_at')->type('DateTime')->defaultValue(date('Y-m-d H:i:s'))->mandatory(true);
		$this->addField('created_time')->defaultValue(date("H:i:s"));
		$this->addField('status')->setValueList(['active'=>'Active','deactive'=>'Deactive'])->defaultValue('active')->mandatory(true);
		$this->addField('is_verified')->type('boolean')->defaultValue(false);
		$this->addField('password_hash');
		$this->addField('otp')->defaultValue(rand(100000,999999));
		$this->addField('updated_at')->type('DateTime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('is_active')->type('boolean')->defaultValue(false)->mandatory(true);
		$this->addField('verification_code');
		$this->addField('type')->setValueList(['superadmin'=>'Super Admin','admin'=>'Admin','user'=>'User','host'=>'Host'])->mandatory(true)->defaultValue('user');
		$this->addField('dob')->type('date');
		$this->addField('mobile');
		$this->addField('received_newsletter')->type('boolean')->defaultValue(true);
		
		//used only for the hungry login 
		$this->add('filestore/Field_File','image_id')->mandatory(true);

		$this->addExpression('profile_image_url')->set(function($m,$q){
			return $q->expr("IFNULL([0],IFNULL([1],[2]))",[
							$m->refSQL('AccessToken')->addCondition('social_app','Facebook')->fieldQuery('profile_picture_url'),
							$m->refSQL('AccessToken')->addCondition('social_app','Google')->fieldQuery('profile_picture_url'),
							$m->refSQL('AccessToken')->addCondition('social_app','HungryDunia')->fieldQuery('profile_picture_url')

						]);	
		});
		$this->addField('gender')->enum(['Male','Female']);
		$this->addField('is_blocked')->type('boolean')->defaultValue(0);
		$this->addField('referral_code');

		$this->addField('street')->mandatory(true);
		$this->addField('zip')->type('int')->mandatory(true);
		$this->addField('address')->mandatory(true);
		$this->addField('extra_info')->type('text');

		// $this->addField('client_id')->type('text'); //encryption of uuid
		// $this->addField('client_secret')->type('text'); //

		// social user
		// $this->addField('social_app')->enum(['Facebook','Google','HungryDunia']);
		// $this->addField('social_access_token')->type('text');
		// $this->addField('access_token_expire_on')->type('DateTime');
		// $this->addField('return_userid');
		// $this->addField('social_content')->type('text');
		// $this->addField('profile_picture_url')->type('text');
		
		$this->addField('age_range');

		$this->hasMany('Review','user_id');
		$this->hasMany('Comment','user_id');
		$this->hasMany('DiscountCoupon','user_id');
		$this->hasMany('ReservedTable','user_id');
		$this->hasMany('UserEventTicket','user_id');
		$this->hasMany('Rating','user_id');
		$this->hasMany('AccessToken','user_id');
		$this->hasMany('ReferralUser','referral_user_id');
		
		$this->addHook('afterSave',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		
        if($this->isDirty("password")&&$this["password"]){
        	if(isset($this->api->auth))
            	$this["password"] = $this->api->auth->encryptPassword($this["password"],$this['email']);
        	else{
            	$this->api->auth->usePasswordEncryption();
            	$this["password"] = $this->api->auth->encryptPassword($this["password"],$this['email']);
        	}
        }
	}

	function afterSave(){

		if(!$this['referral_code']){
			$this['referral_code'] = strtoupper('HNG'.$this->id.rand(111,999));
			$this->save();
		}
	}

	function checkOPTExpire($otp,$user_email=null){
		$user_model = $this;
		if($user_email){
			$user_model = $this->add('Model_User')->addCondition('email',$user_email);
		}

		if($user_model->count()->getOne() > 1){
			return false;
		}

		$user_model->tryLoadAny();
		if(!$user_model->loaded())
			return false;

		
		$config = $this->add('Model_Configuration')->tryLoad(1);
		if(strtotime($this->app->today) > strtotime($user_model['created_at']))
			return false;

		
		$counter = $config['registration_opt_expire_minute']?:30;

		$user_expire_time = strtotime("+".$counter." minutes", strtotime($user_model['created_time']));
		$current_time = strtotime(date("H:i:s"));
		
		// echo $user_expire_time." :: ".$current_time .":: ".date("H:i:s");
		// exit;
		if($current_time > $user_expire_time){
			
			return false;
		}

		if($user_model['otp'] === $otp)
			return true;

		return false;
	}

	function checkEmailMobileExit($email=null,$contact=null){

		$old_user = $this->add('Model_User');

		if($this->loaded()){
			$old_user->addCondition('id','<>',$this->id);
		}

		$old_user->addCondition(
								$old_user->dsql()
									->orExpr()
									->where('email',$email)
									->where('mobile',$contact)
								);
		$old_user->tryLoadAny();
		
		if($old_user->loaded())
			return $old_user;

		return false;
	}

	function getAccessModel($social_app){
		if(!$this->loaded())
			throw new \Exception("Error Processing Request", 1);
		
		if($social_app){
			$access_model = $this->add('Model_AccessToken');
			$access_model->addCondition('user_id',$this->id);
			$access_model->addCondition('social_app',$social_app);
			$access_model->tryLoadany();
			if($access_model->loaded())
				return $access_model;

			return false;
		}

		$access_model = $this->add('Model_AccessToken');
		$access_model->addCondition('user_id',$this->id);
		$access_model->addCondition('social_app',"Facebook");
		$access_model->tryLoadany();
		if($access_model->loaded())
			return $access_model;

		$access_model = $this->add('Model_AccessToken');
		$access_model->addCondition('user_id',$this->id);
		$access_model->addCondition('social_app',"Google");
		$access_model->tryLoadany();
		if($access_model->loaded())
			return $access_model;

		$access_model = $this->add('Model_AccessToken');
		$access_model->addCondition('user_id',$this->id);
		$access_model->addCondition('social_app',"HungryDunia");
		$access_model->tryLoadany();
		if($access_model->loaded())
			return $access_model;

	}

	function sendWelcomeMail($host_business_id=null,$host_business_type=null){
		if(!$this->loaded())
			throw new \Exception("user not found", 1);
		
		if($this['type'] == "host" && $host_business_id && $host_business_type){

			if($host_business_type =="restaurant"){
				$business_model = $this->add('Model_Restaurant');
			}elseif($host_business_type =="event"){
				$business_model = $this->add('Model_Event');
			}elseif($host_business_type =="destination"){
				$business_model = $this->add('Model_Destination');
			}

			$business_model->tryLoad($host_business_id);
			if(!$business_model->loaded())
				throw new \Exception("Business not found", 1);
			if($business_model['user_id'] != $this->id)
				throw new \Exception("Business not belong to you", 1);

			$email_template = $this->add('Model_EmailTemplate')
                                ->addCondition('name',"WELCOMEEMAILHOST")->tryLoadAny();
			$subject = $email_template['subject'];
            $body = $email_template['body'];

			// throw new \Exception($host_business_id." = ".$host_business_type." = ".$this['type']);

            $body = str_replace("{owner_name}", $this['name'], $body);
            $body = str_replace("{restaurant_name}", $business_model['name'], $body);
            $body = str_replace("{email_id}", $this['email'], $body);
            $body = str_replace("{address}", $business_model['address'], $body);
            
		}elseif($this['type'] == "user"){
			
			$email_template = $this->add('Model_EmailTemplate')
                                ->addCondition('name',"WELCOMEEMAILUSER")->tryLoadAny();
         	$subject = $email_template['subject'];
            $body = $email_template['body'];
            $body = str_replace("{user_name}", $this['name'], $body);
            $body = str_replace("{email_id}", $this['email'], $body);
		}

		$outbox = $this->add('Model_Outbox');
        $outbox->sendEmail($this['email'],$subject,$body,$this);
	}

	function send($to="techrakesh91@gmail.com",$subject="Here is the subject",$body="This is the HTML message body <b>in bold!{activation_link}</b>"){
		$config = $this->add('Model_Configuration')->tryLoad(1);

		$mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = $config['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $config['username'];                 // SMTP username
        $mail->Password = $config['password'];                 // SMTP password
        $mail->SMTPSecure = $config['smtp_secure'];             // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $config['port']; 

        $mail->setFrom($config['from_email'], 'Mailer');
        $mail->addReplyTo($config['reply_to'], 'Information');

        $mail->addAddress($to, $this['name']);     // Add a recipient
        // $mail->addAddress('techrakesh91@gmail.com');               // Name is optional
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
            // echo 'Message could not be sent.';
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            return true;
            // echo 'Message has been sent';
        }
	}

	function sendAppRegistrationWelcomeMail(){
		$body = "Welcome to Hungry Dunia !! ".$this['otp']." is your verfication code . Please enter this OTP to verify your identity .";
		$subject = "Welcome to Hungry Dunia !!";
		$this->send($this['email'],$subject,$body);
	}

	function sendOTP(){
		if(!$this->loaded())
			throw new \Exception("user model must loaded for otp", 1);
		
		$this['opt'] = rand(100000,999999);
		$this->save();

		$sms_str = "Welcome to Hungry Dunia !! ".$this['otp']." is your verfication code . Please enter this OTP to verify your identity .";
		$this->add('Controller_SMS')->sendMessage($this['mobile'],$sms_str);

	}

	function markVerify($user_email,$otp){
		$user = $this->add('Model_User')
			->addCondition('email',$user_email)
			->addCondition('otp',$otp);
		
		if($user->count()->getOne() != 1)
			return false;

		$user->tryLoadAny();
		$user['is_verified'] = true;
		$user['is_active'] = true;
		$user->save();
		return true;
	}


	function todayDiscount(){
		if(!$this->loaded())
			throw new \Exception("Model Must Loaded");

		return $this->add('Model_DiscountCoupon')->addCondition('user_id',$this->id)->addCondition('created_at',$this->api->today)->count()->getOne();

	}

	function todayReservedTable(){
		if(!$this->loaded())
			throw new \Exception("Model Must Loaded");
		return $this->ref('ReservedTable')
					->addCondition('booking_date',$this->api->today)
					->count()
					->getOne();
	}

	function getVerificationURL(){

		$this['verification_code'] = strtoupper(substr(md5(rand(111111,999999)),5,6));
        $this->save();

        $url = "http://hungrydunia.com/frontend/public/?page=verification&hungryverification=>true&verification_code=".$this['verification_code']."&email=".$this['email'];
        return $url;
	}

	function sendVerificationLink(){
		if(!$this->loaded())
			throw new \Exception("model must loaded");

		$body = "Your Verification Link {reset_password_link}";
		$subject = "Hungrydunia Verification Link";

		//generating verfication and sending
		$this['verification_code'] = strtoupper(substr(md5(rand(111111,999999)),5,6));
        $this->save();

        $url = $this->api->url('verification',['hungryverification'=>true,'verification_code'=>$this['verification_code'],'email'=>$this['email']]);
        $body = str_replace("{activation_link}", $url, $body);

		$this->send($this['email'],$subject,$body);
	}

	function sendForgotPasswordLink(){
		if(!$this->loaded())
			throw new \Exception("model must loaded");

		$email_template = $this->add('Model_EmailTemplate')
                            ->addCondition('name',"FORGOTPASSWORD")->tryLoadAny();
		$subject = $email_template['subject'];
        $body = $email_template['body'];

		$this['password_hash'] = strtoupper(md5(rand(111111,999999)));
        $this->save();
        $url = $this->api->url('forgotpassword',['password_hash'=>$this['password_hash'],'email'=>$this['email']]);
 		$url = "http://hungrydunia.com/".$url;
        $body = str_replace("{reset_password_link}", $url, $body);
        
		$this->send($this['email'],$subject,$body);
	}

	function getReview($from_date=false,$to_date=false,$limit=null){
		$data = array();
		if(!$this->loaded())
			return $data;
		// throw new \Exception($this->app->now);
		
		$review_model = $this->add('Model_Review')->addCondition('user_id',$this->id)->setOrder('id','desc');
		if($to_date)
			$review_model->addCondition('created_at','<=',$to_date);

		if($from_date)
			$review_model->addCondition('created_at','>=',$from_date);

		if($limit)
			$review_model->setLimit($limit);

		return $review_model->getRows(['id','title','comment','created_at','created_time','is_approved','restaurant','restaurant_id','rating','destination','destination_id']);
	}

	function getDiscount($from_date=false,$to_date=false,$limit=false){
		$data = array();
		if(!$this->loaded())
			return $data;
		
		$dc_model = $this->add('Model_DiscountCoupon')->setOrder('id','desc');
		$dc_model->addCondition('user_id',$this->id);
		if($from_date)
			$dc_model->addCondition('created_at','>=',$from_date);
		if($to_date)
			$dc_model->addCondition('created_at','<=',$to_date);

		if($limit)
			$dc_model->setLimit($limit);

		$dc_model->setOrder('created_at','desc');
		return $dc_model->getRows();
	}

	function getReserveTable($from_date=false,$to_date=false,$limit=null){
		$data = array();
		if(!$this->loaded())
			return $data;

		// throw new \Exception($from_date);
		
		$rt_model = $this->add('Model_ReservedTable')->setOrder('id','desc');
		$rt_model->addCondition('user_id',$this->id);
		if($from_date)
			$rt_model->addCondition('booking_date','>=',$from_date);
			
		if($to_date)
			$rt_model->addCondition('booking_date','<=',$to_date);

		if($limit)
			$rt_model->setLimit($limit);

		return $rt_model->getRows();
	}

	function getRating($from_date=false,$to_date=false){
		$data = array();
		if(!$this->loaded())
			return $data;

		$rt_model = $this->add('Model_Rating')->setOrder('id','desc');
		$rt_model->addCondition('user_id',$this->id);
		if($from_date)
			$rt_model->addCondition('created_at','>=',$from_date);
			
		if($to_date)
			$rt_model->addCondition('created_at','<=',$to_date);

		return $rt_model->getRows();
	}

	function getEventTicket($from_date=false,$to_date=false,$limit=null){
		$data = array();
		if(!$this->loaded())
			return $data;
		
		$event_ticket_model = $this->ref('UserEventTicket')->setOrder('id','desc');

		if($from_date)
			$event_ticket_model->addCondition('created_at','>=',$from_date);
			
		if($to_date)
			$event_ticket_model->addCondition('created_at','<=',$to_date);
		
		if($limit)
			$event_ticket_model->setLimit($limit);
		
		foreach ($event_ticket_model->getRows() as $ticket_asso) {
			$ticket = $this->add('Model_Event_Ticket')->load($ticket_asso['event_ticket_id']);
			$event = $this->add('Model_Event')->load($ticket['event_id']);

			$data[]	= [
						"event_name"=>$event['name'],
						"event_id"=>$event['id'],
						"event_address"=>$event['address'],
						"event_image"=>$event['display_image'],
						"event_day"=>$ticket['event_day'],
						"event_time"=>$ticket['event_time'],

						"ticket_booking_no"=>$ticket_asso['ticket_booking_no'],
						"ticket_id"=>$ticket['id'],
						"ticket_name"=>$ticket['name'],
						"detail"=>$ticket['detail'],

						'qty'=>$ticket_asso['qty'],
						"price"=>$ticket_asso['price'],
						"offer_percentage"=>$ticket_asso['offer_percentage'],
						'total_amount'=>$ticket_asso['total_amount'],
						'offer_amount'=>$ticket_asso['offer_amount'],
						'net_amount'=>$ticket_asso['net_amount'],
						"amount_paid"=>$ticket_asso['amount_paid'],
						"booking_name"=>$ticket_asso['booking_name'],
						"status"=>$ticket_asso['status'],
						"payment_mode"=>$ticket_asso['payment_mode'],

						'created_at'=>$ticket_asso['created_at']
					];
		}

		return $data;
	}

	function getAllListing(){
		if(!$this->loaded())
			throw new \Exception("user model must loaded");
			
		$listing = [];
		$rest = $this->add('Model_Restaurant')->addCondition('user_id',$this->id);
		foreach ($rest as $restaurant) {
			$listing[$restaurant->id."-Restaurant"] = $restaurant['name'];
		}

		$destination = $this->add('Model_Destination')->addCondition('user_id',$this->id);
		foreach ($destination as $dest) {
			$listing[$dest->id."-Destination"] = $dest['name'];
		}

		$event = $this->add('Model_Event')->addCondition('user_id',$this->id);
		foreach ($event as $et) {
			$listing[$et->id."-Event"] = $et['name'];
		}

		return $listing;
	}

}