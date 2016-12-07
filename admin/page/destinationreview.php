<?php

class page_destinationreview extends page_admindestination{

	function init(){
		parent::init();

		$review_crud  =$this->add('CRUD');
		$review_model = $this->add('Model_Review')->addCondition('destination_id','>',0)->setOrder('id','desc');
		$review_crud->setModel($review_model,['destination_id','user_id','title','rating','comment','created_at','is_approved'],['destination','user','title','rating','comment','created_at','created_time','is_approved']);

		$review_crud->grid->add('VirtualPage')
			->addColumn('comments')
			->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
				$page->add('CRUD')->setModel($this->add('Model_Comment')->addCondition('review_id',$id));
				
			});

		$grid = $review_crud->grid;
		$grid->addFormatter('title','Wrap');
		$grid->addFormatter('user','Wrap');
		$grid->addPaginator($ipp=50);
		$grid->addQuickSearch(['destination','title','user']);

	}
}