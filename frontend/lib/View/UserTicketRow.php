<?php

class View_UserTicketRow extends CompleteLister{
	public $template = "view/userticketrow";

	function init(){
		parent::init();

	}

	// function setModel($model){
	// 	parent::setModel($model);
	// }

    function formatRow(){
        $this->current_row['event_display_url'] = "http://hungrydunia.com/".str_replace("public/", "", $this->model['event_url']);
        $this->current_row['booking_date_human'] = date("Y-M-d (D)",strtotime($this->model['booking_date']));
        parent::formatRow();
    }

	function defaultTemplate(){
		return [$this->template];
	}
}