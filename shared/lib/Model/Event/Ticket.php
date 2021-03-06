<?php

class Model_Event_Ticket extends SQL_Model{
	public $table = "event_ticket";
	public $time_title_field = "name";
	function init(){
		parent::init();

		$this->hasOne('Event','event_id')->mandatory(true);
		$this->hasOne('Event_Time','event_time_id',$this->time_title_field)->mandatory(true);

		$this->addField('name')->mandatory(true); // Days
		$this->addField('price')->type('money')->mandatory(true);

		$this->addField('detail')->type('text')->mandatory(true);
		// $this->addField('offer')->type('text')->mandatory(true);
		// $this->addField('applicable_offer_qty')->type('int')->mandatory(true);
		// $this->addField('offer_percentage')->type('int')->mandatory(true);
		$this->addField('max_no_to_sale')->type('int')->mandatory(true);
		$this->addField('disclaimer')->type('text')->mandatory(true);

		$this->addField('is_voucher_applicable')->type('boolean')->defaultValue(true);

		$this->addExpression('event_day')->set(function($m,$q){
			return $m->refSQL('event_time_id')->fieldQuery('on_date');
		});

		$this->addExpression('event_day_id')->set(function($m,$q){
			return $m->refSQL('event_time_id')->fieldQuery('event_day_id');
		});

		$this->addExpression('display_image_id')->set(function($m,$q){
			return $m->refSQL('event_id')->fieldQuery('display_image_id');
		});

		$this->addExpression('event_image')->set(function($m,$q){
			return $m->refSQL('event_id')->fieldQuery('display_image');
		});

		$this->addExpression('event_name')->set(function($m,$q){
			return $m->refSQL('event_id')->fieldQuery('name');
		});


		$this->addExpression('remaining_ticket')->set(function($m,$q){
			return $q->expr("( IFNULL([0],0) - IFNULL([1],0) )",[$m->getElement('max_no_to_sale'),$m->refSQL('UserEventTicket')->addCondition('status','paid')->sum('qty')]);
		});

		$this->hasMany('UserEventTicket','event_ticket_id');

		$this->addHook('beforeSave',[$this,'beforeSave']);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if($this['event_id']){
			$e_model = $this->add('Model_Event')->load($this['event_id']);
			if($e_model['is_free_ticket']){
				throw $this->exception('you cannot add ticket, it\'s free event  ', 'ValidityCheck')->setField('name');
			}
		}
	}
}