<?php

include('Crypto.php');

class page_checkout extends Page{
    public $invoice =null;
    public $test_url = "https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction";
    public $secure_url = "https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction";
    
    function init(){
        parent::init();

        if(!$this->api->auth->model->id){
         $this->add('View_Login',['reload'=>"parent"]);
         return;
        }
        
        
        $invoice_id = $this->app->recall('hungryevent-checkout-saleinvoice');
        if(!$invoice_id AND $_GET['order_id'])
            $invoice_id = $_GET['order_id'];

        if($invoice_id){
            $this->invoice = $this->add('Model_Invoice')->tryLoad($invoice_id);
        }

        // check authentiction
        if($this->invoice AND $this->invoice->loaded() AND $this->invoice['user_id'] != $this->app->auth->model->id){
            $this->add('View_Error')->set('Order does not belongs to you');
            return;
        }

        // $step = $this->api->recall('hungryevent-checkout-step')?$this->api->recall('hungryevent-checkout-step'):"One";
        $step = $this->app->stickyGET('step');
        $step = $step?$step:"One";
        
        if($_GET['order_id'] === $invoice_id AND ($_GET['hstatus'] === 'success' OR $_GET['hstatus'] === 'failure'))
            $step = "Complete";

        $this->view = $this->add('View');
        try{
            $this->{"step$step"}();
            // $this->app->forget("hungryevent-checkout-step");
        }catch(Exception $e){
            // remove all database tables if exists or connetion available
            // remove config-default.php if exists
            throw $e;
        }   

        // Payment Managment
        if($this->api->recall('hungryevent-checkout-paynow')){
            if(!$this->invoice->loaded()){
                $this->add('View_Error')->set("Checkout invoice Not Found");
                $this->api->forget('hungryevent-checkout-paynow');
                $this->api->forget('hungryevent-checkout-saleinvoice');
                return;
            }
            
            // ccavRequestHandler
            error_reporting(0);            
            $configuration = $this->add('Model_Configuration')->tryLoad(1);
            
            $merchant_data='';
            $working_key=$configuration['working_key'];//Shared by CCAVENUES
            $access_code=$configuration['access_code'];//Shared by CCAVENUES
            $merchant_id=$configuration['merchant_id'];//Shared by CCAVENUES
                

            $redirect_url = $this->secure_url;
            if($configuration['test_mode'])
                $redirect_url = $this->test_url;
            
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
            $param = [
                        'tid'=>rand(100,999).$this->invoice->id,
                        'merchant_id'=>$merchant_id,
                        'order_id'=>$this->invoice->id,
                        'amount' => $this->invoice['net_amount'],
                        'currency' => 'INR',
                        'redirect_url' => $protocol.$_SERVER['HTTP_HOST'].$this->api->url(null,array('order_id'=>$this->invoice->id,'hstatus'=>"success"))->getURL(),
                        'cancel_url' => $protocol.$_SERVER['HTTP_HOST'].$this->api->url(null,array('order_id'=>$this->invoice->id,'hstatus'=>"failure"))->getURL(),
                        'language' => 'EN',

                        'billing_name'=>$this->invoice['billing_name'],
                        'billing_address'=>$this->invoice['billing_address'],
                        'billing_city'=>$this->invoice['billing_city'],
                        'billing_state'=>$this->invoice['billing_state'],
                        'billing_zip'=>$this->invoice['billing_zip'],
                        'billing_country'=>$this->invoice['billing_country'],
                        'billing_tel'=>$this->invoice['billing_tel'],
                        'billing_email'=>$this->invoice['billing_email'],
                        'description' => 'Payment Against Event Ticket',
                        'headerImageUrl' => 'http://hungrydunia.com/assets/img/hungrydunia/logo.png'
                    ];

            foreach ($param as $key => $value){
                $merchant_data.=$key.'='.urlencode($value).'&';
            }

            $this->api->forget('hungryevent-checkout-paynow');
            $encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.

            // $this->add('Model_Cart')->emptyCart();
            $this->app->redirect($this->app->url($redirect_url,['encRequest'=>$encrypted_data,'access_code'=>$access_code]));
        }
    }

    function stepOne(){

        $this->template->trySet('step_ticket','active');
        $this->template->trySet('step_address','disabled');
        $this->template->trySet('step_payment','disabled');
        $this->template->trySet('step_complete','disabled');

        $cart = $this->add('Model_Cart');

        $form = $this->view->add('Form',null,null,['form/stacked']);
        $form->addField('line','primary_booking_name')->validateNotNull()->set($this->app->auth->model['name']);
        $form->addField('line','secondary_booking_name');
        $form->addField('Number','mobile')->validateNotNull()->set($this->app->auth->model['mobile']);
        $form->addField('line','email')
                    ->set($this->app->auth->model['email'])
                    ->validateNotNull()
                    ->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')
                    ;

        $form->addSubmit('Next');
        if($form->isSubmitted()){
            
            if(strlen($form['mobile']) != 10)
                $form->error('mobile','not verified');
            
            // save sale invoice
            $invoice = $this->add('Model_Invoice');
            $invoice['user_id'] = $this->api->auth->model->id;
            $invoice->save();

            foreach ($cart as $cart_ticket) {
                $event_ticket_model = $this->add('Model_Event_Ticket')
                    ->addCondition('id',$cart_ticket['event_ticket_id']);
                $event_ticket_model->tryLoadAny();
                if(!$event_ticket_model->loaded())
                    continue;

                // $applicable_offer = 0;
                // if($cart_ticket['qty'] >= $event_ticket_model['applicable_offer_qty'])
                //     $applicable_offer = $event_ticket_model['offer_percentage'];

                $book_ticket_model = $this->add('Model_UserEventTicket');
                $booked_ticket_model = $book_ticket_model->bookTicket(
                                $this->app->auth->model->id,
                                $cart_ticket['event_ticket_id'],
                                $form['primary_booking_name'],
                                $form['secondary_booking_name'],
                                $cart_ticket['qty'],
                                $event_ticket_model['price'],
                                $cart_ticket['discount_voucher'],
                                $cart_ticket['discount_amount'],
                                true,
                                $invoice->id
                            );

                //todo send the sms and email
                //$booked_ticket_model->send();
            }
            $this->add('Model_Cart')->emptyCart();
            $this->app->memorize('hungryevent-checkout-saleinvoice',$invoice->id);
            // $this->app->memorize('hungryevent-checkout-step',"Address");
            $form->js(true,$this->js()->reload(['step'=>"Address"]))->execute();
        }

    }

    function stepPayment(){
        $this->template->trySet('step_ticket','complete');
        $this->template->trySet('step_address','complete');
        $this->template->trySet('step_payment','active');
        $this->template->trySet('step_complete','disabled');
    }

    function stepAddress(){
        
        if(!$this->invoice->loaded()){
            $invoice_id = $this->app->recall('hungryevent-checkout-saleinvoice');
            $this->invoice = $this->add('Model_Invoice')->load($invoice_id);
        }

        $this->template->trySet('step_ticket','complete');
        $this->template->trySet('step_address','active');
        $this->template->trySet('step_payment','disabled');
        $this->template->trySet('step_complete','disabled');

        $address_form = $this->add('Form',null,null,['form/stacked']);
        $col = $address_form->add('Columns');
        $col1 = $col->addColumn(4);
        $col2 = $col->addColumn(8);

        $col1->addField('billing_name')->validateNotNull(true);
        $col2->addField('billing_address')->validateNotNull(true);

        $col_2 = $address_form->add('Columns');
        $col2_1 = $col_2->addColumn(4);
        $col2_2 = $col_2->addColumn(4);
        $col2_3 = $col_2->addColumn(4);

        $col2_1->addField('billing_city')->validateNotNull(true);
        $col2_2->addField('billing_state')->validateNotNull(true);
        $col2_3->addField('billing_country')->validateNotNull(true);

        $col_3 = $address_form->add('Columns');
        $col3_1 = $col_3->addColumn(4);
        $col3_2 = $col_3->addColumn(4);
        $col3_3 = $col_3->addColumn(4);

        $col3_1->addField('billing_zip')->validateNotNull(true);
        $col3_2->addField('billing_tel')->validateNotNull(true);
        $col3_3->addField('billing_email')
                ->validateNotNull(true)
                ->set($this->app->auth->model['email'])
                ->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');

        $checkbox_field = $address_form->addField('checkbox','shipping_address_same_as_billing_address')->set(true);

        $s_col = $address_form->add('Columns');
        $s_col1 = $s_col->addColumn(4);
        $s_col2 = $s_col->addColumn(8);

        $s_col1->addField('delivery_name');
        $s_col2->addField('delivery_address');

        $s_col_2 = $address_form->add('Columns');
        $s_col2_1 = $s_col_2->addColumn(4);
        $s_col2_2 = $s_col_2->addColumn(4);
        $s_col2_3 = $s_col_2->addColumn(4);

        $s_col2_1->addField('delivery_city');
        $s_col2_2->addField('delivery_state');
        $s_col2_3->addField('delivery_country');

        $s_col_3 = $address_form->add('Columns');
        $s_col3_1 = $s_col_3->addColumn(4);
        $s_col3_2 = $s_col_3->addColumn(4);
        $s_col3_3 = $s_col_3->addColumn(4);

        $s_col3_1->addField('delivery_zip');
        $s_col3_2->addField('delivery_tel');
        $s_col3_3->addField('delivery_email');
        
        $checkbox_field->js(true)->univ()->bindConditionalShow([
                ''=>['delivery_name','delivery_address','delivery_city','delivery_state','delivery_country','delivery_zip','delivery_tel','delivery_email'],
                '*'=>['']
            ],'div.atk-form-row');


        $address_form->addSubmit('Next');
        if($address_form->isSubmitted()){
            
            $this->invoice['billing_name'] = $address_form['billing_name'];
            $this->invoice['billing_address'] = $address_form['billing_address'];
            $this->invoice['billing_city'] = $address_form['billing_city'];
            $this->invoice['billing_state'] = $address_form['billing_state'];
            $this->invoice['billing_country'] = $address_form['billing_country'];
            $this->invoice['billing_zip'] = $address_form['billing_zip'];
            $this->invoice['billing_tel'] = $address_form['billing_tel'];
            $this->invoice['billing_email'] = $address_form['billing_email'];

            if(!$address_form['shipping_address_same_as_billing_address']){
                if(!$address_form['delivery_name'])
                    $address_form->error('delivery_name','Delivery Name is a mandatory field');

                if(!$address_form['delivery_address'])
                    $address_form->error('delivery_address','Delivery Address is a mandatory field');

                if(!$address_form['delivery_city'])
                    $address_form->error('delivery_city','Delivery City is a mandatory field');
                
                if(!$address_form['delivery_state'])
                    $address_form->error('delivery_state','Delivery State is a mandatory field');
                
                if(!$address_form['delivery_country'])
                    $address_form->error('delivery_country','Delivery Country is a mandatory field');

                if(!$address_form['delivery_zip'])
                    $address_form->error('delivery_zip','Delivery zip is a mandatory field');

                if(!$address_form['delivery_tel'])
                    $address_form->error('delivery_tel','Delivery Tel is a mandatory field');

                if(!$address_form['delivery_email'])
                    $address_form->error('delivery_email','Delivery Email is a mandatory field');
                
                if(!filter_var($address_form['delivery_email'], FILTER_VALIDATE_EMAIL))
                    $address_form->error('delivery_email','Error in Delivery email');

                $this->invoice['delivery_name'] = $address_form['delivery_name'];
                $this->invoice['delivery_address'] = $address_form['delivery_address'];
                $this->invoice['delivery_city'] = $address_form['delivery_city'];
                $this->invoice['delivery_state'] = $address_form['delivery_state'];
                $this->invoice['delivery_country'] = $address_form['delivery_country'];
                $this->invoice['delivery_zip'] = $address_form['delivery_zip'];
                $this->invoice['delivery_tel'] = $address_form['delivery_tel'];
                $this->invoice['delivery_email'] = $address_form['delivery_email'];

            }else{
                $this->invoice['delivery_name'] = $address_form['billing_name'];
                $this->invoice['delivery_address'] = $address_form['billing_address'];
                $this->invoice['delivery_city'] = $address_form['billing_city'];
                $this->invoice['delivery_state'] = $address_form['billing_state'];
                $this->invoice['delivery_country'] = $address_form['billing_country'];
                $this->invoice['delivery_zip'] = $address_form['billing_zip'];
                $this->invoice['delivery_tel'] = $address_form['billing_tel'];
                $this->invoice['delivery_email'] = $address_form['billing_email'];
            }

            $this->invoice = $this->invoice->save();

            $this->app->memorize('hungryevent-checkout-paynow',true);
            // $this->app->memorize('hungryevent-checkout-step',"Complete");
            $address_form->js(true,$this->js()->reload(['step'=>"Payment"]))->execute();
        }
    }

    function stepComplete(){

        $this->template->trySet('step_ticket','complete');
        $this->template->trySet('step_address','complete');
        $this->template->trySet('step_payment','complete');
        $this->template->trySet('step_complete','complete');

        if(!$this->invoice->loaded()){
            $this->add('View_Error')->set('Order Not Found');
            return;
        }

        $wrapper = $this->add('View')
                    ->addClass('promo-box')
                    ->setStyle(['padding'=>'20px','text-align'=>'center'])
                    ;
        // error_reporting(0);
        $configuration = $this->add('Model_Configuration')->tryLoad(1);
        $workingKey=$configuration['working_key'];//Shared by CCAVENUES
        // $access_code=$configuration['access_code'];//Shared by CCAVENUES
        // $merchant_id=$configuration['merchant_id'];//Shared by CCAVENUES

        $encResponse=$_POST["encResp"];         //This is the response sent by the CCAvenue Server
        $rcvdString=decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);
        
        // echo "<center>";

        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('=',$decryptValues[$i]);
            if($i==3)   $order_status=$information[1];
        }

        $view = "View_Error";
        if($order_status==="Success")
        {   
            $msg =  "Thank you for Booking with us. Your Account has been charged and your transaction is successful. We will keep you posted regarding the status of your order through e-mail";
            $view = "View_Success";
            $this->invoice['status'] = "Paid";
        }
        else if($order_status==="Aborted")
        {
            $msg =  "Thank you for Booking with us.We will keep you posted regarding the status of your order through e-mail";
            $view = "View_Warning";
            $this->invoice['status'] = "Aborted";
        
        }
        else if($order_status==="Failure")
        {
            $msg = "Thank you for Booking with us.However,the transaction has been declined.";
            $this->invoice['status'] = "Failure";
        }
        else
        {
            $msg  = "Security Error. Illegal access detected";
            $this->invoice['status'] = "Cancled";
        }

        // echo "<br><br>";

        // echo "<table cellspacing=4 cellpadding=4>";
        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('=',$decryptValues[$i]);
            $key = $information[0];
            $value = urldecode($information[1]);
            
            if(in_array($key, ['tracking_id','bank_ref_no','order_status','payment_mode','card_name','amount','trans_date'])){
                $this->invoice[$key] = $value;
            }
            // echo '<tr><td>'.$information[0].'</td><td>'.urldecode($information[1]).'</td></tr>';
        }

        // echo "</table><br>";
        // echo "</center>";

        $this->invoice['transaction_detail']  = json_encode($decryptValues,true);
        $this->invoice->save();

        $wrapper->add($view)->set($msg);

        $wrapper->add('Button')->set('Continue Booking')->js('click')->univ()->redirect('index');
        $this->app->forget('hungryevent-checkout-saleinvoice');
        $this->app->forget('hungryevent-checkout-step');
        $this->app->forget('hungryevent-checkout-paynow');
    }

    function defaultTemplate(){
    	return ['page/checkout'];
    }
}