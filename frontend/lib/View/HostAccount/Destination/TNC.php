<?php

class View_HostAccount_Destination_TNC extends View{
	function init(){
		parent::init();

		$this->add('View',null,null,['view/destinationtnc']);
	}
}