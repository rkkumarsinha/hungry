<?php
class page_verification extends Page
{
    function init(){
        parent::init();

        $email = $this->api->stickyGET('email');
        $verification_code = $this->api->stickyGET('verification_code');

        $user = $this->add('Model_User')
        		->addCondition('email',$_GET['email'])
        		;
        if($user->count()->getOne() > 1){
        	$this->add('View_Error',null,'verification')->set('more tha 1 email id exist');
        	return;
        }
        $user->tryLoadAny();

      	$form = $this->add('Form',null,'verification',['form/stacked']);
      	$form->addField('Readonly','email')->set($email);
      	$form->addField('Hidden','email_copy')->set($email);
      	$form->addField('verification_code')->set($verification_code);
      	$form->addSubmit('Verify');

      	if($form->isSubmitted()){
      		$user = $this->add('Model_User')
      				->addCondition('email',$form['email_copy'])
      				->addCondition('verification_code',$form['verification_code'])
      				;
      		$user->tryLoadAny();
      		if(!$user->loaded()){
      			$form->displayError('verification_code','verification wrong try agin');
      		}

      		$user['is_verified'] = true;
      		$user['is_active'] = true;
          $user->save();
      		$this->api->stickyForget('email');
      		$this->api->stickyForget('verification_code');
          
          $this->api->memorize('from','verification');
      		$form->js()->univ()->redirect($this->api->url('signin'))->execute();
          // ->univ()->successMessage('Verification successfully')->execute();
      	}
    }

    function defaultTemplate(){
    	return ['page/verification'];
    }
}

