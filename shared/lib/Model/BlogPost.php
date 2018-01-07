<?php

class Model_BlogPost extends SQL_Model{
	public $table = "blog_post";

	function init(){
		parent::init();

		$this->hasOne('BlogCategory','blog_category_id');

		$this->addField('name')->mandatory(true);
		$this->addField('slug')->mandatory(true);
		$this->add('filestore/Field_File','post_image_id')->hint('image dimension: 300 * 300 px');
		$this->addField('tag')->type('text')->hint('comma seperated multiple values');
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('author');
		$this->addField('detail')->type('text');

		$this->addField('is_active')->type('boolean')->defaultValue(1);
		$this->addField('order')->defaultValue(0)->hint('decending order');

		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('beforeSave',$this);
	}
	function beforeSave(){
		$this['slug'] = $this->app->normalizeName($this['slug']);
	}
}