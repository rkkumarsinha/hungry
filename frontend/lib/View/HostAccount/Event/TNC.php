<?php

class View_HostAccount_Event_TNC extends View{
	function init(){
		parent::init();

		$model = $this->add('Model_Configuration')->tryLoadAny();
		$this->add('View')->setHtml($model['event_tnc']);
	}
}