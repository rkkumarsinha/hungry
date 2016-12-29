<?php
class View_Restaurant_ReserveTable extends View{
    public $restaurant_id;
    function init(){
        parent::init();

        $restaurant_id = $this->restaurant_id;
        //check for the login
        //if not logged in
            //show the login and registration page
        if(!$this->api->auth->model->id){
            $this->add('View_Login',['reload_page'=>true]);
            $this->js(true)->_selector('.reservetable-hungry-submit')->hide();
            return;
        }

        // $v = $this->add('View');
        if($_GET['reservation_id']){
            if($_GET['reservation_id'] == "delete"){
                $this->add('View_Error')->set('please try agin, failed due to technical or internet connection');
            }else{
                $this->add('View_Success')->setHtml('Hi '.$this->app->auth->model['name'].'<br/> thank you for making reservation with hungrydunia.<br/> your reservation id: <b>'.$_GET['reservation_id'].'</b> is being processed you will shortly receive confirmation email/ sms. <br/>show the confirmation email/sms at restaurant to claim your reservation and offer.');
            }
            $this->js(true)->_selector('.reservetable-hungry-submit')->hide();

            return;
        }

        //check for the discount coupon max limit three in a day (before 12:0 clock)
        //today discount return count of discount before 12:00PM
        $discount_count = $this->api->auth->model->todayReservedTable();
        if($discount_count === 3){
            $this->add('View_Error')->set('you exceed your today limit, try tomorrow');
            $this->js(true)->_selector('.reservetable-hungry-submit')->hide();
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
                $this->js(true)->_selector('.reservetable-hungry-submit')->hide();

                return;
            }

            //loading Restaurant model
            $restaurant = $this->add('Model_Restaurant')->load($restaurant_id);
            // $discount = $this->add('View');
            // $discount->add('View')->set('Flat Discount 20%');

            $this->js(true)->_selector('.reservetable-hungry-submit')->show();
            $form = $this->add('Form',null,null,['form/stacked']);
            $c = $form->add('Columns');
            $c1 = $c->addColumn(7);
            $c2 = $c->addColumn(5);

            // $c1->addField('Radio',"offers",'Flat Discount and Offers')->setValueList($restaurant->getOfferAndDiscount())->validateNotNull();

            $c1->addField('line','name','Book Table For')->set($this->api->auth->model['name']);
            
            $user_model = $this->add('Model_User')->load($this->app->auth->model->id);

            $c1->addField('line','email')->set($user_model['email']);
            $c1->addField('line','mobile')->set($user_model['mobile']);

            $row = $c1->add('Columns');
            $row_col1 = $row->addColumn(6);
            $row_col2 = $row->addColumn(6);

            $row_col1->addField('Number','adult')->validateNotNull();
            $row_col2->addField('Number','child')->validateNotNull();

            $row2 = $c1->add('Columns')->addClass('input-padding-remove');
            $row2_col1 = $row2->addColumn(5);
            $row2_col2 = $row2->addColumn(4);
            $row2_col3 = $row2->addColumn(3);

            $date_picker = $row2_col1->addField('DatePicker','booking_date')->validateNotNull();
            $time = $row2_col2->addField('dropdown','time')->validateNotNull();
            $time_array = [];
            $hour = 00;
            $minute = 00;
            for ($hour = 00; $hour < 12; $hour++) { 
                for ($minute = 0; $minute <= 60 ; $minute= $minute + 15) {
                    $value = $hour." : ".$minute;
                    $time_array[$value] = $value;
                }
            }
            $time->setValueList($time_array);

            $row2_col3->addField('dropdown','period')->setValueList(['AM'=>'AM','PM'=>'PM']);


            $c2->addField('radio','discount_or_offer')->setValueList($restaurant->getOfferAndDiscount())->validateNotNull();

            $c2->addField('text','request')->setAttr('PlaceHolder','Your Special Request (optional)');
            $c2->addField('Checkbox','agree_with_terms_and_condition');

            // $c2->addSubmit('Book Table')->addClass('hungry-green-btn');
            $this->js('click',$form->js()->submit())->_selector('.reservetable-hungry-submit');

            if($form->isSubmitted()){
                
                if(!$form['agree_with_terms_and_condition'])
                    $form->displayError('agree_with_terms_and_condition','you must agree with our terms and condition');

                $rt_model = $this->add('Model_ReservedTable');
                $rt_model['user_id'] = $this->api->auth->model->id;
                $rt_model['restaurant_id'] = $restaurant_id;
                $rt_model['book_table_for'] = $form['name'];
                $rt_model['no_of_adult'] = $form['adult']?:0;
                $rt_model['no_of_child'] = $form['child']?:0;
                $rt_model['email'] = $form['email'];
                $rt_model['mobile'] = $form['mobile'];
                $rt_model['booking_date'] = $form['booking_date'];
                $rt_model['booking_time'] = date("H:i", strtotime($form['time']." ".$form['period']));
                $rt_model['message'] = $form['request'];

                $temp_array = explode("_", $form['discount_or_offer']);
                if($temp_array[0] === "d"){
                    $rt_model['discount_id'] = $temp_array[1];

                    $rt_model['discount_offer_value'] = "Flat ".$restaurant['discount_percentage_to_be_given']." %";
                }
                
                if($temp_array[0] === 'o'){
                    $rt_model['restoffer_id'] = $temp_array[1];
                    $offer_model = $this->add('Model_RestaurantOffer')->load($temp_array[1]);
                    
                    $rt_model['discount_offer_value'] = $offer_model['name']." ".$offer_model['sub_name']." ".$offer_model['detail'];
                }


                try{
                    $rt_model->save();
                    $rt_model->sendProcessingSMS();
                    $this->js()->univ()->reload(['reservation_id'=>$rt_model['booking_id']])->execute();
                }catch(\Exception $e){
                    $rt_model->delete();
                    $this->js()->univ()->reload(['reservation_id'=>"delete"])->execute();
                }
                $this->js(true)->_selector('.reservetable-hungry-submit')->hide();
            }
    }

    // function defaultTemplate(){
    //     return ['page/getdiscount'];
    // }
}

