<?php

class page_hostaccount_yourlisting extends Page{
    function init(){
        parent::init();

        $this->add('View_Info')->set("your listing");
	}
}
