<?php

class page_keyword extends Page{

	 public $title='Keyword';

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Keyword');
		$crud->grid->addHook('formatRow',function($g){

			if($g->model['image_id']){
				$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
				$f->tryLoadAny();
				if($f->loaded()){
					$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
					$g->current_row_html['image'] = "<img src=".$path.">";
				}else
					$g->current_row_html['image'] = "No Icon Found";
			}else
				$g->current_row_html['image'] = "No Icon Found";
		});
	}
}