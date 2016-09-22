<?php
class page_reservetable extends Page{

    function init(){
        parent::init();

        $restaurant_id = $this->api->stickyGET('restaurant_id');

        //check for the login
        //if not logged in
        	//show the login and registration page
        if(!$this->api->auth->model->id){
        	$this->add('View_Login',['reload'=>"parent"]);
        	return;
        }


        // $col = $this->add('Columns');
        // $col1 = $col->addColumn(6);
        // $col2 = $col->addColumn(6);

        //else
        	//check for the discount coupon max limit three in a day (before 12:0 clock)
        	//today discount return count of discount before 12:00PM
        $discount_count = $this->api->auth->model->todayReservedTable();
        if($discount_count === 3){
        	$this->add('View_Error')->set('you exceed your today limit, try tomorrow');
        	return;
        }

        //check for restaurant today discount //only one user can take one discount on each restaurant in one day
        	$rt = $this->add('Model_ReservedTable')
        			->addCondition('user_id',$this->api->auth->model->id)
        			->addCondition('restaurant_id',$restaurant_id)
        			->addCondition('booking_date',$this->api->today)
        			;
        	$rt->tryLoadAny();
        	if($rt->loaded()){
        		$this->add('View_Info')->set('already reserved table');
        		// return;
        	}

            //loading Restaurant model
            $restaurant = $this->add('Model_Restaurant')->load($restaurant_id);
            // $discount = $this->add('View');
            // $discount->add('View')->set('Flat Discount 20%');

            $v = $this->add('View');
            if($_GET['reservation_id']){
                if($_GET['reservation_id'] == "delete"){
                    $v->add('View_Error')->set('please try agin, failed due to internet connection');
                    return;
                }

                $v->add('View_Success')->setHtml('Hi '.$this->app->auth->model['name'].'<br/> thank you for making reservation with hungrydunia.<br/> your reservation id: <b>'.$_GET['reservation_id'].'</b> is being processed you will shortly receive confirmation email/ sms. <br/>show the confirmation email/sms at restaurant to claim your reservation and offer.');
                return;
            }

        	$form = $v->add('Form',null,null,['form/stacked']);
            $c = $form->add('Columns');
            $c1 = $c->addColumn(6);
            $c2 = $c->addColumn(6);

            // $c1->addField('Radio',"offers",'Flat Discount and Offers')->setValueList($restaurant->getOfferAndDiscount())->validateNotNull();

            $c1->addField('line','name','Book Table For')->set($this->api->auth->model['name']);
            $c1->addField('Number','adult')->validateNotNull();
            $c1->addField('Number','child')->validateNotNull();
            $date_picker = $c1->addField('DatePicker','booking_date')->validateNotNull();

            $time = $c1->addField('Time','time');
            $time->afterField()->addField('dropdown','period')->setValueList(['AM'=>'AM','PM'=>'PM']);

            $c2->addField('line','email')->set($this->api->auth->model['email']);
            $c2->addField('line','mobile')->set($this->api->auth->model['mobile']);

            $c2->addField('radio','discount_or_offer')->setValueList($restaurant->getOfferAndDiscount());
            
            $c2->addField('text','request')->setAttr('PlaceHolder','Your Special Request (optional)');
            $c2->addField('Checkbox','agree_with_terms_and_condition');

            $c2->addSubmit('Book Table')->addClass('hungry-green-btn');
            
            if($form->isSubmitted()){
                
                if(!$form['agree_with_terms_and_condition'])
                    $form->displayError('agree_with_terms_and_condition','you must agree with our terms and condition');

                $rt_model = $this->add('Model_ReservedTable');
                $rt_model['user_id'] = $this->api->auth->model->id;
                $rt_model['restaurant_id'] = $restaurant_id;
                $rt_model['book_table_for'] = $form['name'];
                $rt_model['no_of_person'] = ($form['adult']?:0) + ($form['child']?:0);
                $rt_model['email'] = $form['email'];
                $rt_model['mobile'] = $form['mobile'];
                $rt_model['booking_date'] = $form['booking_date'];
                $rt_model['booking_time'] = $form['booking_time'];
                $rt_model['message'] = $form['request'];
                // $rt['discount_or_offer_id'] =$form['discount_or_offer'];
                $temp_array = explode("_", $form['discount_and_offer']);
                if($temp_array[0] == "d")
                    $rt_model['discount_id'] = $temp_array[1];
                
                if($temp_array[0] == 'o')
                    $rt_model['offer_id'] = $temp_array[1];

                $rt_model->save();
                //first send the email or sms then save
                try{
                    $rt_model->sendReservedTable($form['email'],$form['mobile']);
                    $v->js()->univ()->reload(['reservation_id'=>$rt_model['name']])->execute();
                }catch(\Exception $e){
                    $rt_model->delete();
                    $v->js()->univ()->reload(['reservation_id'=>"delete"])->execute();
                }
                
            }
    }

    // function defaultTemplate(){
    //     return ['page/getdiscount'];
    // }
}

