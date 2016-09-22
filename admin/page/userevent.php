<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_userevent extends Page {

    public $title='User Event';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $crud->setModel('UserEventTicket');
    }

}
