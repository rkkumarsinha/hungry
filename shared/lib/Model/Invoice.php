<?php

class Model_Invoice extends SQL_Model{
	public $table = "invoice";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('name');
		$this->addField('status')->setValueList(['Draft','Due','Paid','Aborted','Failure','Cancled'])->defaultValue('Due');
		
		$this->addField('billing_name');
		$this->addField('billing_address')->type('text');
		$this->addField('billing_city');
		$this->addField('billing_state');
		$this->addField('billing_zip');
		$this->addField('billing_country');
		$this->addField('billing_tel');
		$this->addField('billing_email');
		
		$this->addField('delivery_name');
		$this->addField('delivery_address')->type('text');
		$this->addField('delivery_city');
		$this->addField('delivery_state');
		$this->addField('delivery_zip');
		$this->addField('delivery_country');
		$this->addField('delivery_tel');
		$this->addField('delivery_email');

		$this->addField('tracking_id');
		$this->addField('bank_ref_no');
		$this->addField('order_status');
		$this->addField('payment_mode');
		$this->addField('card_name');
		$this->addField('amount');
		$this->addField('trans_date');

		$this->addField('transaction_detail')->type('text');

		$this->hasMany('UserEventTicket','invoice_id');
		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('UserEventTicket')->sum('net_amount')]);
		});
		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){

		// generate Unique Invoice number
		if(!$this['name'])
			$this['name'] = strtoupper('HNG'.$this->id.rand(111,999));
	}

}