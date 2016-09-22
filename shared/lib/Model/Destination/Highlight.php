<?php

class Model_Destination_Highlight extends SQL_Model{
	public $table = "destination_highlight";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->add('filestore/Field_Image','image_id');

		$this->addField('type')->setValueList(['occasion'=>'occasion','facility'=>'facility','service'=>'service'])->mandatory('true');

		$this->hasMany('Destination_HighlightAssociation','destination_highlight_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}