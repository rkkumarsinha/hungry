<?php

class View_RedefineSearch extends \View{
	function init(){
		parent::init();

		$this->add('View_Search');
	}

	function defaultTemplate(){
		return ['view/redefinesearch'];
	}
}