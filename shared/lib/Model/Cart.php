<?php

 class Model_Cart extends \Model{

	function init(){
		parent::init();

		$this->setSource('Session');
		
		$this->addField('name');
		$this->addField('event_ticket_id');
		$this->addField('qty');
		$this->addField('event_time_id');
		$this->addField('event_time');
		$this->addField('event_day_id');
		$this->addField('event_day');
		$this->addField('unit_price')->type('money');
		$this->addField('disclaimer');
		$this->addField('sequence')->type('Number')->defaultValue(0);
	}

	function addTicket($event_ticket_id,$event_ticket,$qty,$event_time_id, $event_time,$event_day_id,$event_day,$unit_price,$disclaimer){
		$this->unload();

		if(!is_numeric($qty)) $qty=1;

		if(!is_numeric($event_ticket_id)) return;
		
		$this['event_ticket_id'] = $event_ticket_id;
		$this['name'] = $event_ticket;
		$this['qty'] = $qty;
		$this['event_time_id'] = $event_time_id;
		$this['event_time'] = $event_time;
		$this['event_day_id'] = $event_day_id;
		$this['event_day'] = $event_day;
		$this['unit_price'] = $unit_price;
		$this['disclaimer'] = $disclaimer;
		$this['sequence'] = $this->getNextSequence();
		$this->save();
	}

	function getNextSequence(){
		$cart = $this->add('Model_Cart');
		$max_number = 0;
		foreach ($cart as $c) {
			if($c['sequence'] > $max_number)
				$max_number = $c['sequence'];
		}

		return $max_number	+ 1;
	}

	function getEventCount(){
		$cart = $this->add('Model_Cart');
		$count = 0;
		foreach ($cart as $cart_item) {
			$count ++;
		}

		return $count;
	}

	function emptyCart(){
		$this->add('Model_Cart')->deleteAll();
	}

	function updateItem($event_ticket_id,$event_ticket_name,$event_time_id,$event_time,$event_day_id,$event_day,$unit_price,$new_qty,$disclaimer){
		if(!$this->loaded())
			throw new \Exception("cart model must loaded");
		
		$this['event_ticket_id'] = $event_ticket_id;
		$this['name'] = $event_ticket_name;
		$this['qty'] = $new_qty;
		$this['event_time_id'] = $event_time_id;
		$this['event_time'] = $event_time;
		$this['event_day_id'] = $event_day_id;
		$this['event_day'] = $event_day;
		$this['unit_price'] = $unit_price;
		$this['disclaimer'] = $disclaimer;
		$this->save();
	}
}
 