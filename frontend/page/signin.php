<?php

class page_signin extends Page{
    function init(){
        parent::init();

        $this->add('View_Login');
    }

    function defaultTemplate(){
    	return ['page/signin'];
    }
}