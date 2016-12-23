<?php

class View_Lister_EventVoucher extends CompleteLister{


	function formatRow(){
		
		// $this->current_row_html['starting_date'] = date('M-d-Y',strtotime($this->model['starting_date']));
		$this->current_row_html['expiry_date'] = date('M-d-Y',strtotime($this->model['expiry_date']));
		
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/event/voucher'];
	}
}