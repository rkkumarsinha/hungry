<?php

class View_HostAccount_Restaurant_Notification extends CompleteLister{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;

		$notification = $this->add('Model_Notification');
		$notification->addCondition('to','Restaurant');
		$notification->addCondition(
							$notification->dsql()->orExpr()
							->where('to_id',$host_restaurant->id)
							->where('to_id',null)
						);
		$notification->addCondition('country_id',$host_restaurant['country_id']);
		$notification->addCondition('state_id',$host_restaurant['state_id']);
		$notification->addCondition(
							$notification->dsql()->orExpr()
								->where('city_id',$host_restaurant['city_id'])
								->where('city_id',null)
						);

		$notification->setOrder('created_at','desc');
		if(!$notification->count()->getOne()){
			$this->add('View_Warning',null,'not_found')->set("no record found");
		}
		$this->setModel($notification);

		$paginator = $this->add("Paginator",null,'Paginator');
        $paginator->setRowsPerPage(10);
		// $grid = $this->add('Grid')->setModel($notification);

	}
	function formatrow(){
		 $this->current_row_html['message'] = $this->model['message'];
		parent::formatRow();
	}

	function setModel($model){
		parent::setModel($model);
	}

	function defaultTemplate(){
		return ['view/notification'];
	}

}