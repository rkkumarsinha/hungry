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
		
		if(!$this['event_id'])
			throw $this->exception('event not found', 'ValidityCheck')->setField('name');

		$event_model = $this->add('Model_Event')->tryLoad($this['event_id']);
		if(!$event_model->loaded())
			throw $this->exception('event not found', 'ValidityCheck')->setField('name');

		if(!($this['on_date'] >= $event_model['starting_date'] && $this['on_date'] <= $event_model['closing_date']) ){
			throw $this->exception('date must in between starting date('.$event_model['starting_date'].') closing date ('.$event_model['closing_date'].')', 'ValidityCheck')->setField('on_date');
		}
	}

}