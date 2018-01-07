<?php
class page_verification extends Page
{
    function init(){
        parent::init();

        $email = $this->api->stickyGET('email');
        $this->api->stickyGET('business');
        $this->api->stickyGET('business_type');
        $verification_code = $this->api->stickyGET('verification_code');

        $event_slug = $this->app->recall('event_slug',false);

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
          if($user['type'] == "user"){
            $user['is_active'] = true;
            $this->api->memorize('from','verification');
          }else{
            $user['is_active'] = false;
            $this->api->memorize('from','verificationhost');
          }

          $user->save();
          $this->api->stickyForget('email');
          $this->api->stickyForget('verification_code');
          

          try{

            if($user['type'] == "user"){
              $user->sendWelcomeMail();
            }

            $this->app->stickyForget('business_type');
            $this->app->stickyForget('business');

            if($event_slug){
              $this->api->auth->loginById($user->id);
              $this->app->redirect($this->app->url('bookticket',['slug'=>$event_slug]));
            }else{
              $form->js()->univ()->redirect($this->api->url('signin'))->execute();
            }

          }catch(Exception $e){
      		  $form->js()->univ()->redirect($this->api->url('signin'))->execute();
          }

      	}
    }

    function defaultTemplate(){
    	return ['page/verification'];
    }
}

