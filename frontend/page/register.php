<?php
class page_register extends Page
{
    function init()
    {
        parent::init();

        $f = $this->add('Form',null,'register',['form/stacked'])->addClass('hungry-registration-form');
        $f->addField('full_name')->validateNotNull(true);
        
        // $day_field = $f->addField('day')->addClass('atk-col-4');//->setValueList(['1'=>1,'2'=>2]);
        // $day_field->afterField()->addField('month')->addClass('atk-col-4');
        // $day_field->afterField()->addField('year')->addClass('atk-col-4');

        $f->addField('DatePicker','date_of_birth')->validateNotNull(true);

        $email = $f->addField('email')->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');

        $f->addField('password','password')->validateNotNull();
        $f->addField('password','confirm_password')->validateNotNull();
        $f->addField('Checkbox','received_newsletter');
        $f->addSubmit('Create an Account');
        if($f->isSubmitted()){
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
            $user['password'] = $f['password'];
            $user['is_verified'] = true;
            $user['dob'] = $f['date_of_birth'];
            $user['received_newsletter'] = $f['received_newsletter'];
            if($user->send($to=$f['email'])){
                $user['is_active'] = true;
                $user->save();
                $f->js(null,$f->js()->reload())->univ()->successMessage('Registered Successfully')->execute();
            }else{
                $f->js(null,$f->js()->reload())->univ()->errorMessage('something happen wrong')->execute();
            }


        }


    }

    function defaultTemplate(){
    	return ['page/register'];
    }
}