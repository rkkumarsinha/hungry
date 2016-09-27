<?php

class page_validate extends Page{
	public $email_not_found;

    function init(){
        parent::init(); 

        $user_id = explode("_", $this->app->stickyGET('email_not_found'))[0];
        
        $user_model = $this->add('Model_User');
        $user_model->tryLoad($user_id);
        if(!$user_model->loaded()){
          $this->add('View_Error')->set("Registration failed ".$user_id);
          return;
        }

        if($user_model['email']){
          $this->add('View_Success')->set("already Registerd");
          $this->api->auth->login($user_model);
          $this->api->redirect($this->api->url('account'));
        }

        $f = $this->add('Form')->addClass('container atk-box')->setStyle(['width'=>'50%','margin'=>"20px auto 20px auto"]);
        $email_field = $f->addField('line','email');
        // $email_field->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')->setAttr('PlaceHolder','enter your email');
        $f->addSubmit("update");
        if($f->isSubmitted()){
            if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)){
              $f->displayError('email','email not valid');
            }

            $user_model['email'] = $f['email'];
            $user_model->save();
           $this->api->auth->login($user_model);
           $this->api->redirect($this->api->url('account'));
        }

    //     // $user_id = explode("_", $_GET['email_not_found'])[0];
		  //   $user_id = $_GET['email_not_found'];
    //    	if(!is_numeric($user_id)){
    //    		$this->add('View_Error')->set("Registration error try again");
    //    		return;
    //    	}

    //    	$model = $this->add('Model_User')->tryLoad($_GET['email_not_found']);
    //    	if(!$model->loaded()){
    //    		$this->add('View_Error')->set("User not found");
    //    		return;
    //    	}

    //    	if($model['email']){
    //    		// set to auth model and redirect
    //    		$this->api->auth->login($model['email']);
    //     	$this->api->redirect($this->api->url('index'));
    //    	}

    //    	$f = $this->add('Form');
    //     $f->addField('line','email')->setAttr('PlaceHolder','enter your email');
    //     $f->addSubmit('Submit');

    //     if($f->isSubmitted()){
    //       throw new \Exception("Error Processing Request", 1);

          
    //     	$user = $this->add('Model_User');
    //         $user->addCondition('email',$f['email']);
    //         $user->addCondition('id','<>',$model->id);
    //         $user->tryLoadAny();
    //         if($user->loaded())
    //             $f->displayError('email','email already exist');

    //     	$model['email'] = $f['email'];
    //     	$model->save();
    //     	$this->api->auth->login($f['email']);
    //     	$this->api->redirect($this->api->url('index'));
    //     }
    // }

    // function defaultTemplate(){
    // 	return ['page/signin'];
    }
}