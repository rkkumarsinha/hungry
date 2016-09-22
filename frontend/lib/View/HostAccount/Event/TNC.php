<?php

class View_HostAccount_Event_TNC extends View{
	function init(){
		parent::init();

		$this->add('View',null,null,['view/eventtnc']);
	}
}