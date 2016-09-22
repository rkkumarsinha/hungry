<?php

class page_configuration extends Page{
	function init(){
		parent::init();
		
		$model = $this->add('Model_Configuration')->tryLoad(1);

		$f = $this->add('Form',null,null,['form/stacked']);
		$f->addSubmit('Update');
		$f->setModel($model);

		$f->onSubmit(function($f){
			$f->update();
			return $f->js()->univ()->successMessage('Saved');
		});
	
		// $this->add('CRUD')->setModel('Configuration');


	}
}