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

        $subs = $this->add('Model_Subscriber');
        $subs->setOrder('created_at','desc');

        $crud = $this->add('CRUD');
        $crud->setModel($subs);
        $crud->grid->addPaginator($ipp=50);
        $crud->grid->addQuickSearch(['name','mobile_no']);
        $crud->add("misc/Export");
    }

    function export(){
        throw new \Exception("Error Processing Request", 1);
        
    }
}
