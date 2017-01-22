<?php

class Model_Destination_HighlightAssociation extends SQL_Model{
	public $table = "destination_highlight_association";
	function init(){
		parent::init();

		$this->hasOne('Destination','destination_id');
		$this->hasOne('Destination_Highlight','destination_highlight_id');

		$this->addExpression('highlight_type')->set(function($m,$q){
			return $m->refSQL('destination_highlight_id')->fieldQuery('type');
		});

		$this->addExpression('is_active')->set(function($m,$q){
			return $m->refSQL('destination_highlight_id')->fieldQuery('is_active');
		});

		$this->addExpression('icon_url')->set(function($m,$q){
			return $q->expr("replace([0],'/public','')",[$m->refSQL('destination_highlight_id')->fieldQuery('image')]);
		});

		// $this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		$old = $this->add('Model_Destination_HighlightAssociation');
		$old->addCondition('destination_id',$this['destination_id'])
			->addCondition('highlight_type',$this['highlight_type'])
			->addCondition('destination_highlight_id',$this['destination_highlight_id'])
			->addCondition('id','<>',$this['id']);
		
		if($old->count()->getOne())
			throw $this->exception('Already Added', 'ValidityCheck')->setField('destination_highlight_id');

	}
}