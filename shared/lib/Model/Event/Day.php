<?php

class Model_Event_Day extends SQL_Model{
	public $table = "event_day";

	function init(){
		parent::init();

		$this->hasOne('Event','event_id');

		$this->addField('name')->mandatory(true)->caption('Name');		
		$this->addField('on_date')->type('date')->mandatory('true');
		$this->hasMany('Event_Time','event_time_id');

		$this->addExpression('event_starting_date')->set($this->refSQL('event_id')->fieldQuery('starting_date'));
		$this->addExpression('event_closing_date')->set($this->refSQL('event_id')->fieldQuery('closing_date'));

		$this->addHook('beforeSave',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');

	}

	function beforeSave(){
		if($this['on_date'] < $this['event_starting_date']){
			throw $this->exception('must be greater then event starting date('.$this['event_starting_date'].')', 'ValidityCheck')->setField('on_date');
		}

		if($this['on_date'] > $this['event_closing_date']){
			throw $this->exception('must be less then event starting date('.$this['event_closing_date'].')', 'ValidityCheck')->setField('on_date');
		}
	}

}