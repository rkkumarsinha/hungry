<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_state extends Page {

    public $title='State';

    function init() {
        parent::init();

        $this->add('CRUD')->setModel('State');
    }

}
