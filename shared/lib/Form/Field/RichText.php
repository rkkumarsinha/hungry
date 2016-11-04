<?php

class Form_Field_RichText extends Form_Field_Text {
	function init(){

		// $this->api->jui->addStaticInclude('ckeditor/ckeditor');
		// $this->api->jui->addStaticInclude('ckeditor/adapters/jquery');
		
		$this->js(true)->_load('ckeditor/ckeditor');
		$this->js(true)->_load('ckeditor/adapters/jquery');
		parent::init();
	}

	function render(){
        $this->js(true)->ckeditor();
		parent::render();
	}

}