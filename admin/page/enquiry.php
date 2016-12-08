<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_enquiry extends Page {

    public $title='Enquiry';

    function init() {
        parent::init();

        $crud = $this->add('CRUD',['allow_add'=>false]);
        $crud->setModel('Enquiry');
    }

}
