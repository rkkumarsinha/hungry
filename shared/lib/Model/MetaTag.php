<?php

class Model_MetaTag extends SQL_Model{
	public $table = "meta_tag";

	function init(){
		parent::init();

	$this->hasOne('SeoConfig','seo_config_id');

	$this->addField('meta_property')->hint('FaceBook: og:title, og:site_name, og:url, og:description, og:image, og:type, og:locale . For Twitter twitter:card, twitter:title, twitter:description, twitter:creator, twitter:url, twitter:image, twitter:image:alt .');
	$this->addField('meta_content')->type('text');

	// $this->add('dynamic_model/Controller_AutoCreator');
	
	// <!--FACEBOOK-->
	// <meta property="og:title" content="" >
	// <meta property="og:site_name" content="">
	// <meta property="og:url" content="www.http://hungrydunia.com" >
	// <meta property="og:description" content="zxchkjc zc" >
	// <meta property="og:image" content="" >
	// <meta property="fb:app_id" content="" >
	// <meta property="og:type" content="website" >
	// <meta property="og:locale" content="" >

	// <!--TWITTER-->
	// <meta property="twitter:card" content="summary" >
	// <meta property="twitter:title" content="" >
	// <meta property="twitter:description" content="" >
	// <meta property="twitter:creator" content="" >
	// <meta property="twitter:url" content="www.http://hungrydunia.com" >
	// <meta property="twitter:image" content="" >
	// <meta property="twitter:image:alt" content="" >

	// <!--GOOGLE+-->
	// <link rel="author" href="">
	// <link rel="publisher" href="">
	}
}