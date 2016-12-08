<?php

class page_destinationenquiry extends page_admindestination{
	function init(){
		parent::init();

		$review_crud  = $this->add('CRUD');
		$review_model = $this->add('Model_DestinationEnquiry');
		$review_crud->setModel($review_model);
	}
}