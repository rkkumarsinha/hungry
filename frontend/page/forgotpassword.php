<?php

class page_forgotpassword extends Page
{
    function init()
    {
        parent::init();

        $email = $this->api->stickyGET('email');
        $password_hash = $this->api->stickyGET('password_hash');

        if($email && $password_hash){
            $form = $this->add('Form',null,'forgotpassword',['form/stacked']);
            $form->addField('hidden','email')->set($email);
            $form->addField('hidden','password_hash')->set($password_hash);
            $form->addField('password','new_password')->validateNotNull();
            $form->addField('password','confirm_password')->validateNotNull();
            $form->addSubmit('Change Password');

            if($form->isSubmitted()){
                if(trim($form['new_password'])!= trim($form['confirm_password']))
                  $form->error('confirm_password',"password and confirm password are not same");

                $user = $this->add('Model_User');
                $user->addCondition('email',$form['email']);
                $user->addCondition('password_hash',$form['password_hash']);
                $user->tryLoadAny();
                if(!$user->loaded())
                    $form->displayError('confirm_password','some thing happen wrong');

                $user['password'] = $form['confirm_password'];
                $user->save();
                $this->api->stickyForget('email');
                $this->api->stickyForget('password_hash');
                $this->api->memorize('from','forgotpassword');
                $form->js(null,$this->js()->univ()->redirect($this->api->url('signin')))->execute();
            }

        }else{
            $form = $this->add('Form',null,'forgotpassword',['form/stacked'])
                    ->addClass('hungry-forgotpassword-form');

            $email_field = $form->addField('email')->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');
            $form->addSubmit('Send Reset Password Link');

            if($form->isSubmitted()){
                $user = $this->add('Model_User')->addCondition('email',$form['email']);
                $user->tryLoadAny();
                if(!$user->loaded()){
                    $form->displayError('email','your email is not registered');
                }

                $user->sendForgotPasswordLink();

                $form->js(null,$form->js()->reload())->univ()->successMessage('reset password link sent to your registered email')->execute();
            }
        }

    }

    function defaultTemplate(){
    	return ['page/forgotpassword'];
    }
}