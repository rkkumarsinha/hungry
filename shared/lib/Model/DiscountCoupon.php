<?php

class Model_DiscountCoupon extends SQL_Model{
	public $table = "discount_coupon";

	function init(){
		parent::init();

		$this->hasOne('Restaurant','restaurant_id');
		$this->hasOne('User','user_id');
		$this->hasOne('Discount','discount_id');
		$this->hasOne('RestaurantOffer','offer_id');

		$this->addField('name')->caption('username');
		$this->addField('email');
		$this->addField('mobile');
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('discount_coupon')->defaultValue(strtoupper(substr(md5(rand(111111,999999)),5,6)));
		$this->addField('discount_taken')->type('money'); // total discount taken
		$this->addField('status')->enum(['redeemed','to be redeemed','unused'])->defaultValue('to be redeemed');
		$this->addField('is_send')->type('boolean')->defaultValue(false);
		$this->addField('total_amount')->type('money')->defaultValue(0);
		$this->addField('amount_paid')->type('money');
		$this->addField('payment_mode')->setValueList(['cash'=>'Cash','card'=>"Card",'imps'=>"IMPS",'e_wallet'=>"E Wallet"]);
		
		$this->addExpression('restaurant_address')->set($this->refSQL('restaurant_id')->fieldQuery('address'));
		$this->addExpression('restaurant_image')->set($this->refSQL('restaurant_id')->fieldQuery('display_image'));
		$this->addExpression('restaurant_name')->set($this->refSQL('restaurant_id')->fieldQuery('name'));
		$this->addField('created_date')->type('date');
		$this->addHook('beforeSave',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['created_date'] = date('Y-m-d',strtotime($this['created_at']));
	}
	// function afterInsert($m){
	// 	$this->sendDiscount();
	// }
	//return true if Quota is exceeded else return true
	function checkDiscountQuota($restaurant_id,$user,$discount_id=null,$offer_id=null){
		// $discount_count = $user->todayDiscount();
		if(!$user->loaded())
			return "user not found";

		$discount_count = $this->add('Model_DiscountCoupon')
				->addCondition('user_id',$user->id)
				->addCondition('user_id','<>',null)
				->addCondition('created_date',$this->api->today)
    			// ->addCondition('restaurant_id',$restaurant_id)
				->count()->getOne();	
        if($discount_count >= 3){
        	return 0;
        	return 'you exceed your today limit, try tomorrow';
        }
        
        //check for restaurant today discount //only one user can take one discount on each restaurant in one day
    	$dc = $this->add('Model_DiscountCoupon')
    			->addCondition('user_id',$user->id)
    			->addCondition('user_id','<>',null)
    			->addCondition('restaurant_id',$restaurant_id)
    			->addCondition('created_date',$this->api->today)
    			;
    	$dc->tryLoadAny();
    	if($dc->loaded()){
    		return 0;
    		return 'you already taken discount today on this restaurant';
    	}

    	return true;
	}

	function sendDiscount($send_email=true,$send_sms=true){
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
		// if(!$this->loaded())
		// 	throw new Exception("model must loaded,discount");

		// $sms_str = "Dear ".$this['user'].", Greetings! ".$this['restaurant'];
		// if($this['discount_taken'])
		// 	$sms_str .= "Flat ".$this['discount_taken']."%";
		// else
		// 	$sms_str .= " Offer ".$this['offer'];

		// $sms_str .= " Code ".$this['discount_coupon'];
		// // $date = date(date('Y-m-d',$this['created_at']), strtotime("+3 days"));
		// $date = strtotime("+3 days", strtotime($this['created_at']));
	 //    $date = date("Y-m-d", $date);
		// $sms_str .= " Expire on ".$date." Rate & review on www.hungrydunia.com. T&C Apply";

		// $this->add('Controller_SMS')->sendMessage($mobile,$sms_str);

		// // $sms_str .= "{discount_code}";
		// // $body = "{discount_code}";
		// // $body = str_replace("{discount_code}", $sms_str, $sms_str);
		// $body = $sms_str;

		// $this->add('Model_User')->send($email,"Your Discount",$body);
	}

	function sendEmail(){
		$email_template = $this->add('Model_EmailTemplate')->addCondition('name',"DISCOUNTCOUPONEMAIL")->tryLoadAny();
		if(!$email_template->loaded()){
			throw new \Exception("email template is missing");
		}

		if(!trim($email_template['subject']))
			throw new \Exception("email template subject missing");

		if(!trim($email_template['body']))
			throw new \Exception("email template body missing");

		$subject = $email_template['subject'];
		$body = $email_template['body'];

		$user_model = $this->add('Model_User')->load($this['user_id']);

		
		if($this['offer_id']){
			$offer = $this->add('Model_RestaurantOffer')->load($this['offer_id']);
			$discount_name = "Offer name: " . $offer['name'] ."<br/>";
			$discount_detail = $offer['detail']."<br/>";
			$discount_coupon = "Offer code: ".$this['discount_coupon'];
		}

		if($this['discount_id']){
			$rest = $this->add('Model_Restaurant')->tryLoad($this['restaurant_id']);
			if(!$rest->loaded())
				throw new \Exception("model rest not loaded", 1);
			// $discount = $this->add('Model_Discount')->load($this['discount_id']);
			$discount_name = "Discount name: Flat ".$rest['discount_percentage_to_be_given']." %<br/>";
			$discount_detail = "";
			$discount_coupon = "Discount code: ".$this['discount_coupon'];
		}

		$expire_date = date("Y-M-d (D)", strtotime("+3 days", strtotime($this['created_at'])));
		// string manupulation
		$body = str_replace("{user_name}", $user_model['name'], $body);
		$body = str_replace("{restaurant_name}", $this['restaurant_name'], $body);
		
		$body = str_replace("{discount_name}", $discount_name, $body);
		$body = str_replace("{discount_detail}",$discount_detail, $body);
		$body = str_replace("{discount_coupon}", $discount_coupon, $body);
		$body = str_replace("{date_time}", $expire_date, $body);
		
		//end of string manupulation
		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail($this['email'],$subject,$body,$user_model);		
		if($email_response != true){
			throw new \Exception($email_response);
		}

		$outbox->createNew("Discount Coupon",$this['email'],$subject,$body,"Email","DiscountCoupon",$this->id,$user_model);
		return true;
	}
	
	function sendSMS(){
		
		$sms_template = $this->add('Model_EmailTemplate');

		//load Offer template if offer id 
		if($this['offer_id']){
			$sms_template->addCondition('name',"DISCOUNTCOUPONOFFERSMS")->tryLoadAny();
			$discount_offer = $this['offer'];
		}else{
			$sms_template->addCondition('name',"DISCOUNTCOUPONSMS")->tryLoadAny();
			$rest = $this->add('Model_Restaurant')->tryLoad($this['restaurant_id']);
			if(!$rest->loaded())
				throw new \Exception("model rest not loaded", 1);
			$discount_offer = "Flat ".($rest['discount_percentage_to_be_given'])." % ";
		}

		if(!$sms_template->loaded())
			throw new \Exception("something wrong, sms template may be delete");
				
		if(!trim($sms_template['body']))
			throw new \Exception("sms template body missing");

		$body = $sms_template['body'];

		//DISCOUNTCOUPONOFFERSMS
		//Dear {$user_name}, Greetings! {$restaurant_name} Offer {$offer_name} Code {$coupon} Expire on {$date}, Rate & review on www.hungrydunia.com. T&C Apply

		//DISCOUNTCOUPONSMS
		// Dear [user_name], Greetings! [restaurant_name] Offer Flat [discount]% Off Code [coupon]. Expire on $date, Rate & review on www.hungrydunia.com. T&C Apply
		$expire_date = date("Y-M-d (D)", strtotime("+3 days", strtotime($this['created_at'])));

		$body = str_replace("{user_name}", $this['name'], $body);
		$body = str_replace("{restaurant_name}", $this['restaurant'], $body);
		$body = str_replace("{offer_name}", $discount_offer, $body);
		$body = str_replace("{coupon}", $this['discount_coupon'], $body);
		$body = str_replace("{date}", $expire_date, $body);
		$body = str_replace("{discount}", $this['discount_taken'], $body);

		$outbox = $this->add('Model_Outbox');
		$sms_response = $outbox->sendSMS($this['mobile'],$body,$this->api->auth->model);
		if($sms_response != true){
			throw new \Exception($sms_response);
		}
		$outbox->createNew("Discount Coupon",$this['mobile'],"SMS",$body,"SMS","DiscountCoupon",$this->id,$this->api->auth->model);
		return true;
	}
}