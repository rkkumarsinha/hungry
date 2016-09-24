<?php

class Model_Highlight extends SQL_Model{
	public $table = "Highlight";
	function init(){
		parent::init();


		$this->addField('name')->caption('Highlight')->mandatory(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->add('filestore/Field_Image','image_id')->mandatory(true);
		
		// $this->add('dynamic_model/Controller_AutoCreator');
	}	
}