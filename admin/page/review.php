<?php

class page_review extends Page{
	function init(){
		parent::init();

		$review_crud  =$this->add('CRUD');
		$review_crud->setModel($this->add('Model_Review')->setOrder('id','desc'));

		$review_crud->grid->add('VirtualPage')
			->addColumn('comments')
			->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
				$page->add('CRUD')->setModel($this->add('Model_Comment')->addCondition('review_id',$id));
				
			});


	}
}