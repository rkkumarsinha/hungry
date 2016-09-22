<?php

class View_HostAccount_Destination_Notification extends CompleteLister{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_destination = $this->app->listmodel;

		$notification = $this->add('Model_Notification');
		$notification->addCondition('to','Destination');
		$notification->addCondition(
							$notification->dsql()->orExpr()
							->where('to_id',$host_destination->id)
							->where('to_id',null)
						);
		$notification->setOrder('created_at','desc');
		if(!$notification->count()->getOne()){
			$this->add('View_Warning',null,'not_found')->set("no record found");
		}
		$this->setModel($notification);

		$paginator = $this->add("Paginator",null,'Paginator');
        $paginator->setRowsPerPage(10);
	}

	function setModel($model){
		parent::setModel($model);
	}

	function defaultTemplate(){
		return ['view/notification'];
	}

}