<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_userevent extends page_adminevent {

    public $title='User Event';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $event = $this->add('Model_UserEventTicket');
        $event->setOrder('created_at','desc');
        $crud->setModel($event);

        $crud->grid->addPaginator($ipp=50);
        $crud->grid->addQuickSearch(['ticket_booking_no','booking_date','mobile','email','booking_name']);
    }
}
