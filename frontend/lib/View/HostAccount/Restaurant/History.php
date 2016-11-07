<?php

class View_HostAccount_Restaurant_History extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;
		
		$tabs = $this->add('Tabs');
		$discount_tab = $tabs->addTab('Discount')->setStyle('overflow','scroll');
		$table_tab = $tabs->addTab('Table Reservation')->setStyle('overflow','scroll');
		$ticket_tab = $tabs->addTab('Ticket')->setStyle('overflow','scroll');
	
		// Discount Coupon
		$dc_model = $discount_tab->add('Model_DiscountCoupon');
		$dc_model->addCondition('restaurant_id',$host_restaurant->id);
		$dc_model->addCondition('created_at','<',$this->api->today);
		$dc_model->setOrder('created_at','desc');

		$discount_offer_voucher = $discount_tab->add('Grid');
		$discount_offer_voucher->setModel($dc_model,['name','email','mobile','created_at','discount_coupon','status','discount','offer','total_amount','amount_paid','payment_mode']);
		$discount_offer_voucher->addPaginator($ipp=20);

		// Table Reservation
		$reserved_table = $table_tab->add('Model_ReservedTable');
		$reserved_table->addCondition('restaurant_id',$host_restaurant->id);
		$reserved_table->addCondition('booking_date','<',$this->api->today);
		$reserved_table->setOrder('booking_date','desc');

		$grid = $table_tab->add('Grid');
		$grid->setModel($reserved_table,['book_table_for','email','mobile','booking_date','booking_time','booking_id','offer_id','status','total_amount','payment_mode','amount_paid']);

		// $ticket_tab
		$ticket_tab->add('View_Warning')->set("Ticket");
	}
}