<?php
class View_Restaurant_GetDiscount extends View{
    public $restaurant_id;

    function init(){
        parent::init();

        $restaurant_id = $this->restaurant_id;

        //check for the login
        //if not logged in
        	//show the login and registration page
        if(!$this->api->auth->model->id){
        	$this->add('View_Login',['reload_page'=>true]);
            $this->js(true)->_selector('.getdiscount-hungry-submit')->hide();
        	return;
        }
        //else
        	//check for the discount coupon max limit three in a day (before 12:0 clock)
        	//today discount return count of discount before 12:00PM
        $discount_count = $this->api->auth->model->todayDiscount();
        if($discount_count >= 3){
        	$this->add('View_Error')->set('you exceed your today limit, try tomorrow');
            $this->js(true)->_selector('.getdiscount-hungry-submit')->hide();
            return;
        }
        
        //check for restaurant today discount //only one user can take one discount on each restaurant in one day
            $dc = $this->add('Model_DiscountCoupon')
                    ->addCondition('user_id',$this->api->auth->model->id)
                    ->addCondition('restaurant_id',$restaurant_id)
                    ->addCondition('created_at',$this->api->today)
                    ;
            $dc->tryLoadAny();
            if($dc->loaded()){
                $this->add('View_Info')->set('you already taken discount today on this restaurant');
                $this->js(true)->_selector('.getdiscount-hungry-submit')->hide();
                return;
            }

            //loading Restaurant model
            $restaurant = $this->add('Model_Restaurant')->load($restaurant_id);
            
            // $discount = $this->add('View');
            // $discount->add('View')->set('Flat Discount 20%');

            $v = $this->add('View');
            if($_GET['reload']){
                $v->add('View_Success')->set('Discount Coupon Send to your registered email id and mobile number.');
                $this->js(true)->_selector('.getdiscount-hungry-submit')->hide();
                return;
            }

            $this->js(true)->_selector('.getdiscount-hungry-submit')->show();
            $form = $v->add('Form',null,null,['form/stacked']);
            $c = $form->add('Columns');
            $c1 = $c->addColumn(6);
            $c2 = $c->addColumn(6);
            
            $user = $this->add('Model_User')->load($this->app->auth->model->id);
            $c1->addField('Radio',"offers",'Select Discount or Offer')->setValueList($restaurant->getOfferAndDiscount())->validateNotNull();
            
            $c2->addField('line','name')->set($user['name']);
            $c2->addField('line','email')->set($user['email']);
            // $form->add('View')->set('Or')->addClass('text-center');
            $c2->addField('line','mobile')->set($user['mobile']);
            $c2->addField('Checkbox','agree_with_terms_and_condition','Agree With Terms and Conditions.');

            $this->js('click',$form->js()->submit())->_selector('.getdiscount-hungry-submit');
            // $c2->addSubmit('Get Code')->addClass('hungry-green-btn');
            if($form->isSubmitted()){

                if(!$form['agree_with_terms_and_condition'])
                    $form->displayError('agree_with_terms_and_condition','you must agree with our terms and condition');

                $dc['name'] = $form['name'];
                $dc['email'] = $form['email'];
                $dc['mobile'] = $form['mobile'];
                
                $offer_array = explode("_", $form['offers']);
                
                if($offer_array[0] === "d"){
                    $dc['discount_taken'] = $restaurant['discount'] - $restaurant['discount_subtract'];
                    $dc['discount_id'] = $restaurant['discount_id'];
                }else
                    $dc['offer_id'] = $offer_array[1];
                
                $dc->save();
                //first send the email or sms then save                
                try{
                    $dc->sendDiscount($form['email'],$form['mobile']);
                }catch(\Exception $e){
                    $dc->delete();
                }
                
                $this->js()->univ()->reload(['reload'=>true])->execute();
            }
    }

    // function defaultTemplate(){
    //     return ['page/getdiscount'];
    // }
}

