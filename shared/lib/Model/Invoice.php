<?php

class Model_Invoice extends SQL_Model{
	public $table = "invoice";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('name');
		$this->addField('status')->setValueList(['Draft','Due','Paid','Cancled'])->defaultValue('Draft');
		
		$this->addField('prn');
		$this->addField('bid');
		$this->addField('amt');
		$this->addField('pid');
		$this->addField('txndatetime');
		$this->addField('transaction_status');

		$this->hasMany('UserEventTicket','invoice_id');
		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('sum([0])',[$m->refSQL('UserEventTicket')->fieldQuery('net_amount')]);
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