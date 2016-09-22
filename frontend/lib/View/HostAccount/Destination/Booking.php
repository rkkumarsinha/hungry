<?php

class View_HostAccount_Destination_Booking extends View{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");
		
		$this->addClass('atk-box');

		$dest_enq = $this->add('Model_DestinationEnquiry');
		$dest_enq->addCondition('status','approved');
		$dest_enq->setOrder('id','desc');

		$grid = $this->add('Grid');
		$grid->setModel($dest_enq,['name','package','occassion','total_budget','created_at','remark']);
		$grid->addPaginator(20);
		$grid->addQuickSearch(['name','package','occassion','package_id','occassion_id','mobile','email']);

	}

	function setModel($model){
		parent::setModel($model);
	}
}