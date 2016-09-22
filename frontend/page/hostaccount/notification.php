<?php

class page_hostaccount_notification extends Page{
    function init(){
        parent::init();

        $this->add('View_Info')->set("hello dashboard layout");
	}
}
