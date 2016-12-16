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

        $step = $this->api->recall('hungryevent-checkout-status')?$this->api->recall('hungryevent-checkout-status'):"One";

        if(!in_array($step, array('Complete','Failed'))){
            $cart_model = $this->add('Model_Cart');
            if(!$cart_model->getEventCount()){
                $this->add('View_Error')->set('your cart is empty');
                return;
            }
        }
        
        if($_GET['order'] AND $_GET['order_id'] === $invoice_id AND $_GET['hstatus'] === 'complete')
            $step = "Complete";

        try{
            $this->{"step$step"}();
            $this->app->forget("hungryevent-checkout-status");
        }catch(Exception $e){
            // remove all database tables if exists or connetion available
            // remove config-default.php if exists
            throw $e;
        }   

        // Payment Managment
        if($this->api->recall('hungryevent-checkout-paynow')){
            $checkout_invoice_id = $this->api->recall('hungryevent-checkout-saleinvoice');
            $this->invoice = $checkout_invoice = $this->add('Model_Invoice')->tryLoad($checkout_invoice_id);
            if(!$checkout_invoice->loaded()){
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
                        'working_key'=>$working_key,
                        'access_code'=>$access_code,
                        'amount' => $this->invoice['net_amount'],
                        'currency' => 'INR',
                        'transactionId' => $this->invoice->id,
                        'description' => 'Payment Against Event Ticket',
                        'headerImageUrl' => 'http://hungrydunia.com/assets/img/hungrydunia/logo.png',
                        'returnUrl' => $protocol.$_SERVER['HTTP_HOST'].$this->api->url(null,array('order_id'=>$this->invoice->id,'hstatus'=>'paid'))->getURL(),
                        'cancelUrl' => $protocol.$_SERVER['HTTP_HOST'].$this->api->url(null,array('canceled'=>'true','order_id'=>$this->invoice->id,'hstatus'=>'failed'))->getURL(),
                        'language' => 'EN',
                        'tid'=>rand(100,999).$this->invoice->id,
                        'merchant_id'=>$merchant_id,
                        'order_id'=>$this->invoice->id,
                        'order_uid'=>"HUN".rand(1000,9999).$this->invoice->id
                    ];

            foreach ($param as $key => $value){
                $merchant_data.=$key.'='.urlencode($value).'&';
            }

            $this->api->forget('hungryevent-checkout-paynow');
            $encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.
                
            // $redirect_url = $this->test_url;

            $this->add('Model_Cart')->emptyCart();
            $this->app->redirect($this->app->url($redirect_url,['encRequest'=>$encrypted_data,'access_code'=>$access_code]));
        }
    }

    function stepOne(){
        $this->add('View_Info')->set('Step 1');
        $cart = $this->add('Model_Cart');

        $form = $this->add('Form');
        $form->addField('line','primary_booking_name')->validateNotNull()->set($this->app->auth->model['name']);
        $form->addField('line','secondary_booking_name');
        $form->addField('Number','mobile')->validateNotNull()->set($this->app->auth->model['mobile']);
        $form->addField('line','email')
                    ->set($this->app->auth->model['email'])
                    ->validateNotNull()
                    ->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')
                    ;

        $form->addSubmit('Proceed To Pay');
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

                $applicable_offer = 0;
                if($cart_ticket['qty'] >= $event_ticket_model['applicable_offer_qty'])
                    $applicable_offer = $event_ticket_model['offer_percentage'];

                $book_ticket_model = $this->add('Model_UserEventTicket');
                $booked_ticket_model = $book_ticket_model->bookTicket(
                                $this->app->auth->model->id,
                                $cart_ticket['event_ticket_id'],
                                $form['primary_booking_name'],
                                $cart_ticket['qty'],
                                $applicable_offer,
                                $event_ticket_model['price'],
                                $form['secondary_booking_name'],
                                true,
                                $invoice->id
                            );

                //todo send the sms and email
                
                //$booked_ticket_model->send();
            }
            // $this->add('Model_Cart')->emptyCart();
            $this->app->memorize('hungryevent-checkout-paynow',true);
            $this->app->memorize('hungryevent-checkout-saleinvoice',$invoice->id);
            $form->js()->redirect($this->api->url())->execute();
        }

    }

    function stepComplete(){
        $this->add('View_Success')->set('Event Booked Successfully');
        error_reporting(0);
        $workingKey='';     //Working Key should be provided here.
        $encResponse=$_POST["encResp"];         //This is the response sent by the CCAvenue Server
        $rcvdString=decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);
        echo "<center>";

        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('=',$decryptValues[$i]);
            if($i==3)   $order_status=$information[1];
        }

        if($order_status==="Success")
        {
            echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
            
        }
        else if($order_status==="Aborted")
        {
            echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
        
        }
        else if($order_status==="Failure")
        {
            echo "<br>Thank you for shopping with us.However,the transaction has been declined.";
        }
        else
        {
            echo "<br>Security Error. Illegal access detected";
        
        }

        echo "<br><br>";

        echo "<table cellspacing=4 cellpadding=4>";
        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('=',$decryptValues[$i]);
                echo '<tr><td>'.$information[0].'</td><td>'.urldecode($information[1]).'</td></tr>';
        }

        echo "</table><br>";
        echo "</center>";
    }

    function defaultTemplate(){
    	return ['page/checkout'];
    }
}