<?php

class View_HostAccount_Restaurant_Sales extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;
		
		$tabs = $this->add('Tabs');
		$discount_tab = $tabs->addTab('Discount');
		$table_tab = $tabs->addTab('Table Reservation');
		$ticket_tab = $tabs->addTab('Ticket');
	
		// Discount Coupon
		$dc_model = $discount_tab->add('Model_DiscountCoupon');
		$dc_model->addCondition('restaurant_id',$host_restaurant->id);
		$dc_model->addCondition('status','redeemed');
		$dc_model->setOrder('created_at','desc');
		$dc_model->getElement('name')->caption('Name');
		$dc_model->getElement('discount_coupon')->caption('Coupon');
		$dc_model->setOrder('created_at','desc');
		$discount_offer_voucher = $discount_tab->add('Grid');
		$discount_offer_voucher->setModel($dc_model,['name','email','mobile','created_at','discount_coupon','discount','offer','total_amount']);
		$discount_offer_voucher->addPaginator($ipp=10);

		// Table Reservation
		$reserved_table = $table_tab->add('Model_ReservedTable');
		$reserved_table->addCondition('restaurant_id',$host_restaurant->id);
		$reserved_table->addCondition('status','verified');
		$reserved_table->setOrder('booking_date','desc');

		$grid = $table_tab->add('Grid');
		$grid->addPaginator($ipp=10);
		$grid->setModel($reserved_table,['book_table_for','email','mobile','booking_date','booking_time','booking_id','offer_id']);
		$grid->addQuickSearch(['email','mobile','book_table_for','booking_date']);
		// $ticket_tab
		$ticket_tab->add('View_Warning')->set("Ticket");

	}
}