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
        $phone = $f->addField('Number','mobile_no')->validateNotNull(true);

        $f->addField('password','password')->validateNotNull();
        $f->addField('password','confirm_password')->validateNotNull();
        $f->addField('Checkbox','received_newsletter');
        $f->addSubmit('Create an Account');
        if($f->isSubmitted()){
            if($f['password']!= $f['confirm_password'])
                $f->error('password',"password and confirm password are not same");

            // check mobile number is valid or 10 digin only
            preg_match_all("/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/", $f['mobile_no'], $matches);
            if(!count($matches[0]))
                $f->displayError('mobile_no','not a valid mobile number');
            
            //check for the email is already exist or not
            $user_m = $this->add('Model_User');
            $user_m->addCondition('mobile',$f['mobile_no']);
            $user_m->tryLoadAny();
            if($user_m->loaded())
                $f->displayError('mobile_no','mobile number already exist');


            //check for the email is already exist or not
            $user = $this->add('Model_User');
            $user->addCondition('email',$f['email']);
            $user->tryLoadAny();
            if($user->loaded())
                $f->displayError('email','email already exist');
                
            $user['name'] = $f['full_name']; 
            $user['email'] = $f['email'];
            $user['password'] = $f['password'];
            $user['is_verified'] = false;
            $user['dob'] = $f['date_of_birth'];
            $user['received_newsletter'] = $f['received_newsletter'];
            $user['is_active'] = true;
            $user['type'] = "user";
            $user['mobile'] = $f['mobile_no'];
            $user->save();
            
            //hungry access
            $md5_access_token = md5(uniqid($user['email']."-".$user['created_at'], true));
            $acc_token = $this->add('Model_AccessToken');
            $acc_token['user_id'] = $user->id;
            $acc_token['social_app'] = "HungryDunia";
            $acc_token['social_access_token'] = $md5_access_token;
            $acc_token->save();

            
            $email_template = $this->add('Model_EmailTemplate')
                                ->addCondition('name',"EMAILVERIFICATIONUSER")->tryLoadAny();
            if(!$email_template->loaded()){
                throw new \Exception("email template is missing");
            }

            if(!trim($email_template['subject']))
                throw new \Exception("email template subject missing");

            if(!trim($email_template['body']))
                throw new \Exception("email template body missing");

            $subject = $email_template['subject'];
            $body = $email_template['body'];

            // verification_email_link
            // user_name
            //Replace Body  Content with Actual Code
            $body = str_replace("{user_name}", $user['name'], $body);
            $body = str_replace("{verification_email_link}", $user->getVerificationURL(), $body);
            
            $outbox = $this->add('Model_Outbox');
            try{
                $email_response = $outbox->sendEmail($user['email'],$subject,$body,$user);
                $outbox->createNew("New User Registered",$user['email'],$subject,$body,"Email","New User Registration",$user->id,$user);
                $this->app->memorize('from','newuser');
                $this->app->redirect($this->app->url('signin'));
                // $f->js(null,$f->js()->reload())->univ()->successMessage('Registered Successfully')->execute();
            }catch(Exception $e){
                // $f->js(null,$f->js()->reload())->univ()->errorMessage('something happen wrong')->execute();
            }

        }
    }

    function defaultTemplate(){
    	return ['page/register'];
    }
}