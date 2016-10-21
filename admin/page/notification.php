<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_notification extends Page {

    public $title='Notification';

    function init() {
        parent::init();

        $crud = $this->add('Grid');
        $crud->setModel($this->add('Model_Notification'));
        $crud->addPaginator($ipp=50);
        $crud->addQuickSearch(['name','created_at','message','from','request_for','status']);
    }
}
