<?php

class page_hostaccount_dashboard extends Page{
    function init(){
        parent::init();

        $this->add('View_Error')->set("hello dashboard layout");
	}
}
