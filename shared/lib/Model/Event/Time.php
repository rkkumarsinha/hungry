<?php

class Model_Event_Time extends SQL_Model{
	public $table = "event_time";

	function init(){
		parent::init();

		$this->hasOne('Event','event_id');
		$this->hasOne('Event_Day','event_day_id')->mandatory(true);
		$this->addField('name')->caption('time'); // Days
		$this->hasMany('Event_Ticket','event_ticket_id');

		$this->addExpression('on_date')->set($this->refSQL('event_day_id')->fieldQuery('on_date'));
		$this->addExpression('event_time_day')->set(function($m,$q){
			return $q->expr('CONCAT([0]," :: ",[1])',
     		    			[
    			    			$m->getElement('event_day'),
    			    			$m->getElement('name')
    			    		]);
		});

		// ->set('CONTACT('.$this->fieldQuery('event_day').')');
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

}