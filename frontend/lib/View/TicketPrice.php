<?php

class View_TicketPrice extends View{
	public $template = "view/ticketprice";

	function init(){
		parent::init();

	}

	function setModel($model){
		parent::setModel($model);

		$event_id = $model;
		//ticket price
        $days = $this->add('Model_Event_Day')->addCondition('event_id',$event_id);
        $str = "";
        foreach ($days as $day) {
            $str .="<h4>".$day['name']."</h4>";
            
            $times = $this->add('Model_Event_Time')->addCondition('event_day_id',$day->id);
            foreach ($times as $time) {
                $str .= "<h5>".$time['name']."</h5>";

                $tickets = $this->add('Model_Event_Ticket')->addCondition('event_time_id',$time->id);
                foreach ($tickets as $ticket) {
                    $str .= "<p>".$ticket['name'].":".$ticket['price']."</p>";
                }
            }
        }

        $this->add('View')->setHtml($str);
	}

	function defaultTemplate(){
		return [$this->template];
	}
}