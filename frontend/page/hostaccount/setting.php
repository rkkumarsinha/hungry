<?php

class page_hostaccount_setting extends Page{
    function init(){
        parent::init();

        $this->add('View_Info')->set("Setting");
	}
}
