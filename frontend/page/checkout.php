<?php

class page_checkout extends Page{

    function init(){
        parent::init();

        if(!$this->api->auth->model->id){
         $this->add('View_Login',['reload'=>"parent"]);
         return;
        }

        $step = $this->api->recall('hungryevent-checkout-status');
        
        if(!in_array($step, array('Complete','Failed'))){
            $cart_model = $this->add('Model_Cart');
            if(!$cart_model->getEventCount()){
                $this->add('View_Error')->set('your cart is empty');
                return;
            }
        }

        $step = $step? $step:"One";
        try{
            $this->{"step$step"}();            
            $this->app->forget("hungryevent-checkout-status");
        }catch(Exception $e){
            // remove all database tables if exists or connetion available
            // remove config-default.php if exists
            throw $e;
        }
    }

    function stepOne(){
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

        $form->addSubmit('Proceed');
        if($form->isSubmitted()){            
            if(strlen($form['mobile']) != 10)
                $form->error('mobile','not verified');
            
            foreach ($cart as $cart_ticket) {
                $event_ticket_model = $this->add('Model_Event_Ticket')->addCondition('id',$cart_ticket['event_ticket_id']);
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
                                true
                            );

                //todo send the sms and email
                
                //$booked_ticket_model->send();
                $this->add('Model_Cart')->emptyCart();
                $this->app->memorize('hungryevent-checkout-status','Complete');
                $form->js()->redirect($this->api->url())->execute();

            }
        }

    }

    function stepComplete(){
        $this->add('View_Success')->set('Event Booked Successfully');
    }

    function defaultTemplate(){
    	return ['page/checkout'];
    }
}