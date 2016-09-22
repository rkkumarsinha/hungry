<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_subscriber extends Page {

    public $title='Subscriber';

    function init() {
        parent::init();

        $this->add('CRUD')->setModel('Subscriber');
    }

}
