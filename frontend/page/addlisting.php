<?php
class page_addlisting extends Page{
    function init()
    {
        parent::init();



        // Thanks for signing up
        // Please check your email and click activate account in the message we just send to email_id.
        // once your account is activated we will verify, and we will send you email with some information to help you get started with hungrydunia.com
        // if you do'nt see the email in your inbox look for it in your junk folder or check your email agaian

        $v = $this->add('View',null,"successsection",['page/addlisting','successsection']);
        $v->setStyle('display','none');
        // $this->template->tryDel('form_section');

        $f = $this->add('Form',null,'register',['form/empty'])->addClass('hungry-registration-form');
        $f->setLayout(['page/addlisting','register']);

        if($this->app->auth->model['type'] != "host"){
            $f->addField('full_name')->validateNotNull(true);
            $email = $f->addField('email')->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');
            $f->addField('password','password')->validateNotNull();
            $f->addField('password','confirm_password')->validateNotNull();
        }else{
            $f->layout->add('View',null,'confirm_wrapper')->set("");
            $f->layout->add('View',null,'full_name_wrapper')->set("");
            $f->layout->add('View',null,'email_wrapper')->set("");
            $f->layout->add('View',null,'password_wrapper')->set("");
            $f->layout->add('View',null,'confirm_wrapper')->set("");
        }

        $f->addField('business_name')->validateNotNull(true);
        $business_type_field = $f->addField('DropDownNormal','business_type')->setValueList(
                        [
                        'cafe'=>"Cafe",
                        'restaurant'=>"Restaurant",
                        "pub" => "Pub",
                        "foodtruck" =>"Food Truck",
                        "foodjoint" =>"Food Joint",
                        "bakery"=>"Bakery",
                        'sweetshop'=>"Sweet Shop",
                        "icecreamparlour"=>"Ice Cream Parlour",
                        "dhabha" => "Dhabha",
                        "venue" => "Venue",
                        'event' => "Event"
                        ]
                    );
        $business_type_field->setEmptyText('Please Select Business Type');
        $business_type_field->validateNotNull(true);
        $mobile = $f->addField("Number",'mobile')->validateNotNull(true);//->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');
        
        $country = $f->addField('DropDownNormal','country');

        $country->setModel($this->add('Model_Country')->addCondition('is_active',true));
        $country->setEmptyText('Please Select Country')->validateNotNull(true);

        $state = $f->addField('DropDownNormal','state');
        $state_model = $this->add('Model_State')->addCondition('is_active',true);
        if($this->app->stickyGET('country_id')){
            $state_model->addCondition('country_id',$_GET['country_id'])->setOrder('name','asc');
            $state->setEmptyText('Please Select State');
        }
        $state->setEmptyText('Please Select State');
        // else{
        //     $state->setAttr('disabled',true);
        //     // $state_model->addCondition('country_id',-1);
        //     $state->addClass('hungrydisabled');
        // }

        // $state->validateNotNull(true);
        $state->setModel($state_model);

        $city = $f->addField('DropDownNormal','city');
        $city_model = $this->add('Model_City')->addCondition('is_active',true);
        if($this->app->stickyGET('state_id')){
            $city_model->addCondition('state_id',$_GET['state_id'])->setOrder('name','asc');
            $city->setEmptyText('Please Select City');
        }
        $city->setEmptyText('Please Select City');
        // else{
        //     $city->setAttr('disabled',true);
        //     // $city_model->addCondition('state_id',-1);
        //     $city->addClass('hungrydisabled');
        // }

        // $city->validateNotNull(true);
        $city->setModel($city_model);

        $area = $f->addField('DropDownNormal','area');
        $area_model = $this->add('Model_Area')->addCondition('is_active',true)->addCondition('is_city',false);
        if($this->app->stickyGET('city_id')){
            $area_model->addCondition('city_id',$_GET['city_id'])->setOrder('name','asc');
            $area->setEmptyText('Please Select Area');
        }
        $area->setEmptyText('Please Select Area');
        // else{
        //     $area->setAttr('disabled',true);
        //     // $area_model->addCondition('city_id',-1);
        //     $area->addClass('hungrydisabled');
        // }
        // $area->validateNotNull(true);
        $area->setModel($area_model);

        $address = $f->addField('Text','address')->validateNotNull(true);
        
        // $day_field = $f->addField('day')->addClass('atk-col-4');//->setValueList(['1'=>1,'2'=>2]);
        // $day_field->afterField()->addField('month')->addClass('atk-col-4');
        // $day_field->afterField()->addField('year')->addClass('atk-col-4');
        // $f->addField('DatePicker','date_of_birth')->validateNotNull(true);

        $country->js('change',$state->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$state->name]),'country_id'=>$country->js()->val()]));
        $state->js('change',$city->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$city->name]),'state_id'=>$state->js()->val()]));
        $city->js('change',$area->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$area->name]),'city_id'=>$city->js()->val()]));

        $f->addField('Checkbox','received_newsletter')->set(true);
        $submit_button = $f->layout->add('Button',null,'submit_button')->set('Submit')->setStyle('width','100%');
        // $f->addSubmit('Create an Account');
        $submit_button->js('click',$f->js()->submit());

        if($f->isSubmitted()){
            
            if(strlen(trim($f['mobile'])) != 10){
                $f->error('mobile',"mobile number must be 10 digit");
            }

            if($this->app->auth->model['type'] != "host"){
                if(trim($f['password'])!= trim($f['confirm_password']))
                    $f->error('password',"password and confirm password are not same");
                

                //check for the email is already exist or not
                $user = $this->add('Model_User');
                $user->addCondition('email',$f['email']);
                $user->tryLoadAny();
                if($user->loaded())
                    $f->displayError('email','email already exist');
                
                $user['name'] = $f['full_name'];
                $user['email'] = $f['email'];
                $user['mobile'] = $f['mobile'];
                $user['password'] = $f['password'];
                $user['is_verified'] = false;
                $user['type'] = "host";
                $user['received_newsletter'] = $f['received_newsletter'];
            }else{
                $user = $this->api->auth->model;
            }

            if($user->send($to=$f['email']) or true){
                $user['is_active'] = true;
                $user->save();

                $business_model = $this->add('Model_Restaurant');
                switch ($f['business_type']) {
                    case 'event':
                            $business_model = $this->add('Model_Event');
                        break;
                    case 'venue':
                            $business_model = $this->add('Model_Destination');
                        break;
                }

                $business_model['name'] = $f['business_name'];
                $business_model['country_id'] = $f['country'];
                $business_model['state_id'] = $f['state'];
                $business_model['city_id'] = $f['city'];
                $business_model['area_id'] = $f['area'];
                $business_model['address'] = $f['address'];
                $business_model['user_id'] = $user->id;
                $business_model->save();

                $js_event = [
                                $v->js()->show(),
                                $v->js()->_selector('.to-top')->trigger('click'),
                                $f->js()->hide()
                            ];
                $f->js(null,$js_event)->univ()->execute();
            }else{
                $f->js(null,$f->js()->reload())->univ()->errorMessage('something happen wrong')->execute();
            }


        }
    }

    function defaultTemplate(){
    	return ['page/addlisting'];
    }
}