<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_report extends Page {

    public $title='Invoice and It\'s Tickets';

    function init() {
        parent::init();


        $model = $this->add('Model_Invoice');
        $model->addExpression('booked_tickets')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('UserEventTicket')->count()]);
		});
        $model->setOrder('id','desc');

        $crud = $this->add('CRUD',['allow_add'=>false]);
        $crud->setModel($model,
        		['user','status','billing_name','billing_address','billing_city','billing_state','billing_zip','billing_country','billing_tel','billing_email','delivery_name','delivery_address','delivery_city','delivery_state','delivery_zip','delivery_country','delivery_tel','delivery_email','tracking_id','bank_ref_no','order_status','payment_mode','card_name','amount','trans_date','transaction_detail'],
        		['user','name','status','billing_city','billing_state','tracking','order_status','amount','trans_date','booked_tickets']
        	);

        $crud->grid->addPaginator(50);

        $crud->grid->add('VirtualPage')
			->addColumn('show_ticket')
			->set(function($page){
				$id = $_GET[$page->short_name.'_id'];

				$ticket_model = $page->add('Model_UserEventTicket');
				$ticket_model->addCondition('invoice_id',$id);

				$grid = $page->add('Grid');
				$grid->setModel($ticket_model);
		});

    }

}
