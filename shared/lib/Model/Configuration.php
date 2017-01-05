<?php

class Model_Configuration extends SQL_Model{
	public $table="configuration";

	function init(){
		parent::init();

		$this->addField('host')->hint('ie. for gmail smtp.gmail.com');
		// $this->addField('smtp_Auth')->enum(['true','false']);
		$this->addField('username')->hint('your email id ie. xyz@doiman.com');
		$this->addField('password')->type('password');
		$this->addField('smtp_secure')->enum(['ssl','tls']);
		$this->addField('port');
		$this->addField('from_email')->hint('your email id ie. xyz@doiman.com');
		$this->addField('reply_to')->hint('your email id ie. xyz@doiman.com');
		$this->addField('footer')->type('text');

		$this->addField('terms_and_conditions')->type('text');

		$this->addField('gateway_url')->hint('http://api.alerts.solutionsinfini.com/v3/?method=sms');
		$this->addField('api_key');
		$this->addField('sender');
		$this->addField('format');
		$this->addField('custom');
		// $this->addField('sms_password_qs_param');
		// $this->addField('sms_number_qs_param');
		// $this->addField('sm_message_qs_param');
		
		//general configuration
		$this->addField('registration_otp_expire_minute')->hint('time in minute');
		
		$this->addField('restaurant_tnc')->type('text')->mandatory(true)->display(array('form'=>'RichText'));
		$this->addField('event_tnc')->type('text')->mandatory(true)->display(array('form'=>'RichText'));
		$this->addField('destination_tnc')->type('text')->mandatory(true)->display(array('form'=>'RichText'));
		
		//ccavenue api setting
		$this->addField('test_mode')->setValueList([1=>'yes',0=>'no']);
		$this->addField('merchant_id');
		$this->addField('access_code');
		$this->addField('working_key');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}