<?php

class Model_Invoice extends SQL_Model{
	public $table = "invoice";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('name');
		$this->addField('status')->setValueList(['Draft'=>'Draft','Due'=>'Due','Paid'=>'Paid','Aborted'=>'Aborted','Failure'=>'Failure','Cancled'=>'Cancled'])->defaultValue('Due');
		
		$this->addField('billing_name');
		$this->addField('billing_address')->type('text');
		$this->addField('billing_city');
		$this->addField('billing_state');
		$this->addField('billing_zip');
		$this->addField('billing_country');
		$this->addField('billing_tel');
		$this->addField('billing_email');
		
		$this->addField('delivery_name');
		$this->addField('delivery_address')->type('text');
		$this->addField('delivery_city');
		$this->addField('delivery_state');
		$this->addField('delivery_zip');
		$this->addField('delivery_country');
		$this->addField('delivery_tel');
		$this->addField('delivery_email');

		$this->addField('tracking_id');
		$this->addField('bank_ref_no');
		$this->addField('order_status');
		$this->addField('payment_mode');
		$this->addField('card_name');
		$this->addField('amount');
		$this->addField('trans_date');

		$this->addField('transaction_detail')->type('text');

		$this->hasMany('UserEventTicket','invoice_id');
		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('UserEventTicket')->sum('net_amount')]);
		});
		$this->addHook('beforeSave',$this);
		$this->addHook('afterSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){

		// generate Unique Invoice number
		if(!$this['name'])
			$this['name'] = strtoupper('HNG'.$this->id.rand(111,999));
		
		if($this['status'] == "Paid"){
			// check has ticket or not
			$booked_tickets = $this->getTickets();
			foreach ($booked_tickets as $ticket_model) {
				$ticket_model['status'] = "paid";
				$ticket_model->save();
			}
		}
	}

	function afterSave(){
		if($this['status'] == "Paid"){
			// check has ticket or not
			$booked_tickets = $this->getTickets();
			foreach ($booked_tickets as $ticket_model) {
				$ticket_model['status'] = "paid";
				$ticket_model->save();
			}
		}
	}
	function getTickets(){
		if(!$this->loaded()) throw new \Exception("invoice model must loaded", 1);

		return $this->add('Model_UserEventTicket')->addCondition('invoice_id',$this->id);
	}

	function send($send_email=true,$send_sms=true){
		if(!$this->loaded())
			throw new Exception("model must loaded, Discount Coupon");

		// if($send_sms){
		// 	$this->sendSMS();
		// }

		if($send_email){
			$this->sendEmail();
		}
	}

	function sendEmail(){

		$email_template = $this->add('Model_EmailTemplate')
								->addCondition('name',"TicketBooking")->tryLoadAny();

		if(!$email_template->loaded()){
			throw new \Exception("email template is missing");
		}

		if(!trim($email_template['subject']))
			throw new \Exception("email template subject missing");

		if(!trim($email_template['body']))
			throw new \Exception("email template body missing");

		$subject = $email_template['subject'];
		$body = $email_template['body'];


		$tickets = $this->add('Model_UserEventTicket')
					->addCondition('invoice_id',$this->id);
		$tickets->addExpression('event_url')->set($tickets->refSQL('event_ticket_id')->fieldQuery('event_image'));
		$tickets->addExpression('event_name')->set($tickets->refSQL('event_ticket_id')->fieldQuery('event'));

		$temp = $this->app->add('View_UserTicketRow');
		$temp->setModel($tickets);
		$html = $temp->getHtml();

		$body = str_replace("{ticket_booked_detail}", $html, $body);

		$outbox = $this->add('Model_Outbox');
		$email_response = $outbox->sendEmail($this['billing_email'],$subject,$body,$this->app->auth->model);
		if($email_response != true){
			throw new \Exception($email_response);
		}

		$outbox->createNew("Event Ticket Booking",$this['billing_email'],$subject,$body,"Email","Event Ticket Booking",$this->id,$this->app->auth->model);
		return true;
	}


	function placeOrderFromWishList($data){

		//check validation here
        // creating user ticket
        $wishlist = $this->add('Model_Wishlist')
        			->addCondition("user_id",$this->app->auth->model->id)
        			->addCondition('is_wishcomplete',false);

       	foreach ($wishlist as $cart) {

			$re_cal_discount_amount = 0;
			$discount_voucher = $cart['discount_voucher'];
			$ticket_model = $this->add('Model_Event_Ticket')
	            			->load($cart['event_ticket_id']);
	        
	        // event ticket condition checked
	        if($cart['qty'] > $ticket_model['remaining_ticket']){
				return array("status"=>"failed","message"=>"tickets sold out");
				exit();
	        }

	        if($cart['unit_price'] != $ticket_model['price']){
				return array("status"=>"failed","message"=>"price mismatch, try again ".$ticket_model['price']." = ".$cart['price']);
	        	exit();
	        }

       		//check discount voucher is applicable or not
			if($discount_voucher){
				if(!$ticket_model['is_voucher_applicable']){
					return [
							'status'=>'failed',
							'message'=>'this voucher ['.$discount_voucher.'] is not applicable on this ticket '.$ticket_model['name']
						];
					exit();
				}

				$voucher = $this->add('Model_Voucher')
							->addCondition('name',$discount_voucher)
							->addCondition('event_id',$ticket_model['event_id']);
				$voucher->tryLoadAny();
				$voucher_status = $voucher->applyCoupon($cart['qty'],$ticket_model['price']);
				if($voucher_status['status'] == "success"){
					$re_cal_discount_amount = $voucher_status['discount_amount'];

				}else{
					return [
							'status'=>'failed',
							'message'=>'this voucher ['.$discount_voucher.'] is not applicable on this ticket '.$ticket_model['name']
						];
					exit();
				}
				
			// 	// if($re_cal_discount_amount != $discount_amount)
			// 	// 	return [
			// 	// 			'status'=>'failed',
			// 	// 			'message'=>'discount amount ['.$discount_amount.'] is not applicable on this event ticket, actual discount amount = '.$re_cal_discount_amount
			// 	// 		];
			}
       	}

       	// saving invoice id
		$this['user_id'] = $this->app->auth->model->id;
		$this['status'] = "Due";
		$this['delivery_name'] = $this['billing_name'] = $data['billing_name'];
        $this['delivery_address'] = $this['billing_address'] = $data['billing_address'];
        $this['delivery_city'] = $this['billing_city'] = $data['billing_city'];
        $this['delivery_state'] = $this['billing_state'] = $data['billing_state'];
        $this['delivery_zip'] = $this['billing_zip'] = $data['billing_zip'];
        $this['delivery_country'] = $this['billing_country'] = $data['billing_country'];
        $this['delivery_tel'] = $this['billing_tel']= $data['billing_tel'];
        $this['delivery_email']= $this['billing_email'] = $data['billing_email'];
        $this->save();
        
        foreach ($wishlist as $cart_ticket) {
        	// just checking event ticket is available or not
	        $event_ticket_model = $this->add('Model_Event_Ticket')
	            ->addCondition('id',$cart_ticket['event_ticket_id']);
	        $event_ticket_model->tryLoadAny();
	        
	        if(!$event_ticket_model->loaded()){
	        	// $this->delete();
	            return ['status'=>'failed','message'=>'ticket not found'];
	            exit();
	        }
	        

	        $book_ticket_model = $this->add('Model_UserEventTicket');
	        $booked_ticket_model = $book_ticket_model->bookTicket(
	                        $this->app->auth->model->id,
	                        $cart_ticket['event_ticket_id'],
	                        $data['primary_booking_name'],
	                        $data['secondary_booking_name'],
	                        $cart_ticket['qty'],
	                        $event_ticket_model['price'],
	                        $cart_ticket['discount_voucher'],
	                        $cart_ticket['discount_amount'],
	                        true,
	                        $this->id,
	                        $cart_ticket['id']
	                    );
	        if(is_array($booked_ticket_model) AND $booked_ticket_model['status'] == "failed"){
	        	// $this->delete();
	        	return $booked_ticket_model;
	        	exit();
	        }

	       // $cart_ticket['is_wishcomplete'] = true;
	       // $cart_ticket->saveAndUnload();
	    }
        return $this;
	}

	function updateInvoiceTransaction($data){
		if(!$this->loaded()) throw new \Exception("order must loaded");
		
		$this['tracking_id'] = $data['tracking_id'];
		$this['bank_ref_no'] = $data['bank_ref_no'];
		$this['order_status'] = $data['order_status'];
		$this['payment_mode'] = $data['payment_mode'];
		$this['card_name'] = $data['card_name'];
		$this['trans_date'] = $data['trans_date'];
		$this['transaction_detail'] = $data['transaction_detail'];
		if($data['order_status'] = "Success")
			$this['status'] = "Paid";
		
		$this->save();
		
		if($this['order_status'] != "Success") return $this;

		$ticket = $this->add('Model_UserEventTicket')
			->addCondition('user_id',$this->app->auth->model->id)
			->addCondition('invoice_id',$this->id)
			;

		foreach ($ticket as $user_ticket) {
			$user_ticket['status'] = "paid";
			$user_ticket->save();
			$wish = $this->add('Model_Wishlist')->load($user_ticket['wishlist_id']);
			$wish['is_wishcomplete'] = true;
			$wish->save();
		}

		return $this;
	}


// <tr><td style="" align=""><img src="http://localhost/hungry//upload/0/20161213232950_0_09.jpg" style="width:100%;"><span style="">event name</span></td>
//                                                         <td width="80%">
//                                                             <table style="" width="100%" cellspacing="0" cellpadding="0">
//                                                                 <tbody><tr><td><h3>{name}Ticket Name{/}</h3>
// <div style="margin-bottom:10px;">Ticket Day and Time</div></td></tr>

// <tr>
//   <td>
//     <table style="width:100%;text-align:right;background:#f2f2f2 !important;">
//       <tbody><tr>
//   	<td style="width:15%;border-right:2px solid white;padding-right:5px;">
//       <strong>Price</strong>
//       <div>{$price}</div>
//   	</td>
//   	<td style="width:15%;border-right:2px solid white;padding-right:5px;">
//       <strong>Quantity</strong>
//       <div>{$quantity}</div>
//   	</td>
//   	 <td style="width:20%;border-right:2px solid white;padding-right:5px;">
//       <strong>Discount Voucher</strong>
//       <div>{$discount_voucher}</div>
//   	</td>
//     	<td style="width:20%;border-right:2px solid white;padding-right:5px;">
//       <strong>Discount Amount</strong>
//       <div>{$discount_amount}</div>
//   	</td>
//     	<td style="width:20%;padding-right:5px;">
//       <strong>Amount</strong>
//       <div>{$net_amount}</div>
//   	</td>
//       </tr>
      
//     </tbody></table><table>

// </table></td></tr>
//                                                             </tbody></table>
//                                                         </td>
//                                                     </tr>

}