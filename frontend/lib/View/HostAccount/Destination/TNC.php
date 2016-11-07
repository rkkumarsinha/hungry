<?php

class View_HostAccount_Destination_TNC extends View{
	function init(){
		parent::init();

		$model = $this->add('Model_Configuration')->tryLoadAny();
		$this->add('View')->setHtml($model['destination_tnc']);
	}
}