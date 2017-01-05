<?php

class page_validate extends Page{
	public $email_not_found;

    function init(){
        parent::init(); 

        $user_id = explode("_", $this->api->stickyGet('email_not_found'))[0];
        
        $user_model = $this->add('Model_User');
        $user_model->tryLoad($user_id);
        if(!$user_model->loaded()){
          $this->add('View_Error')->set("Registration failed ".$user_id);
          return;
        }

        if($user_model['email'] AND $user_model['mobile']){
          $this->add('View_Success')->set("already Registerd");
          $this->api->auth->loginByID($user_model->id);
          // $this->api->auth->login($user_model);
          $this->api->redirect($this->api->url('account'));
        }

        $f = $this->add('Form')->addClass('container atk-box')->setStyle(['width'=>'50%','margin'=>"20px auto 20px auto"]);
        $email_field = $f->addField('line','email')->set($user_model['email']);
        if(!$user_model['mobile'] AND strlen($user_model['mobile']) != 10){
            $f->addField('Number','mobile_no')->set($user_model['mobile']);
        }

        // $email_field->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')->setAttr('PlaceHolder','enter your email');
        $f->addSubmit("update");
        if($f->isSubmitted()){
            if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)){
              $f->displayError('email','email not valid');
            }

            if(!$user_model['mobile'] AND strlen($user_model['mobile']) != 10){
                preg_match_all("/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/", $f['mobile_no'], $matches);
                if(!count($matches[0]))
                    $f->displayError('mobile_no','not a valid mobile number');

                //check for the email is already exist or not
                $user_m = $this->add('Model_User');
                $user_m->addCondition('mobile',$f['mobile_no']);
                $user_m->tryLoadAny();
                if($user_m->loaded())
                    $f->displayError('mobile_no','mobile number already exist');
                
                $user_model['mobile'] = $f['mobile_no'];
            }
                        
            $user_model['email'] = $f['email'];
            $user_model->save();

            $this->api->auth->loginByID($user_model->id);
            $this->api->stickyForget('email_not_found');
            // $this->api->auth->login($user_model);
            $this->api->redirect($this->api->url('account'));
        }

    }
}