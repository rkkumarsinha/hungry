<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_emailtemplate extends Page {

    public $title='Email Template';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $crud->setModel('EmailTemplate');

    }

}
