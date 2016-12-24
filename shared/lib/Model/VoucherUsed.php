<?php

class Model_VoucherUsed extends SQL_Model{
	public $table = "voucher_used";

	function init(){
		parent::init();

		$this->hasOne('Voucher','voucher_id')->mandatory(true);
		$this->hasOne('User','user_id')->mandatory(true);
		$this->hasOne('UserEventTicket','user_event_ticket_id')->mandatory(true);

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->mandatory(true);

		$this->addField('voucher_amount')->type('money');

		// $this->addExpression('event_id')->set($this->refSQL('user_event_ticket_id')->fieldQuery('eventid'));
		// $this->addHook('beforeSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}


}