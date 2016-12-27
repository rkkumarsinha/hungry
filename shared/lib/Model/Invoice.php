<?php

class Model_Invoice extends SQL_Model{
	public $table = "invoice";

	function init(){
		parent::init();

		$this->hasOne('User','user_id');
		$this->addField('name');
		$this->addField('status')->setValueList(['Draft','Due','Paid','Aborted','Failure','Cancled'])->defaultValue('Due');
		
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

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){

		// generate Unique Invoice number
		if(!$this['name'])
			$this['name'] = strtoupper('HNG'.$this->id.rand(111,999));
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