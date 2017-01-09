<?php

class Model_SeoConfig extends SQL_Model{
	public $table = "seo_config";

	function init(){
		parent::init();

// <title>hungrydunia.com</title>
// <link rel="canonical" href="www.http://hungrydunia.com">
// <meta name="viewport" content="width=device-width, initial-scale=1">
// <meta name="keywords" content="sdnjs s cznxc " >
// <meta name="description" content="zxchkjc zc">
	
	$this->addField('page_name');
	$this->addField('title')->type('text');
	$this->addField('keyword')->type('text');
	$this->addField('description')->type('text');

	$this->hasMany('MetaTag','seo_config_id');
	$this->add('dynamic_model/Controller_AutoCreator');
	}
}