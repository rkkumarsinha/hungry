<?php

class Model_Event_Category extends SQL_Model{
	public $table = "event_category";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true)->caption('Event Name');
		$this->add('filestore/Field_Image','image_id')->mandatory(true);
		
		$this->hasMany('Event','event_category_id');

		$this->add('dynamic_model/Controller_AutoCreator');

	}
}