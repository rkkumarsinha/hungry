<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_reservedtable extends Page {

    public $title='Reserved Table';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $crud->setModel('ReservedTable');

    }

}
