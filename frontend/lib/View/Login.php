<?php

class View_Login extends View{
	public $reload;
	public $reload_class;

	function init(){
		parent::init();
		
		if($this->api->auth->model->id){
			$container = $this->add("View")->addClass('container')->setStyle(['width'=>'40%','margin-top'=>'20px']);
            $container->add('View_Info',null)->set('already logged in');
            $container->add('Button',null)->set('Logout')->addClass('atk-swatch-red')->js('click')->redirect($this->api->url('logout'));
			$this->template->tryDel("login_wrapper");
            return;

	    }else{
	        //Redirect Page
	        if($status = $this->api->recall('from')){
	        	$info = $this->add('View_Info');
	        	if($status=="forgotpassword"){
	        		$info->set('Your Password Changed Successfully');
	        	}elseif($status=="verification"){
	        		$info->set('You Account Activated Successfully');
	        	}
	        	$this->api->stickyForget('from');
	        }

	        if($this->api->recall('next_url')){
	            $redirect_url = array('next_url'=>$this->api->recall('next_url'));
	        }

	        $f = $this->add('Form',null,'generalloginform',['form/stacked']);
	        $f->addField('email')->validateNotNull()->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')->setAttr('PlaceHolder','enter your email');
	        $f->addField('password','password')->validateNotNull()->setAttr('PlaceHolder',"enter your password");
	        $f->addSubmit('Login')->addClass('btn-block');
	        
	       	$this->add('View',null,'forgotpassword')
                ->setElement('a')
                ->setAttr('href', $this->api->url('forgotpassword'))
                ->addClass('forgot-password')
                ->set('Forgot Password');

	        if($f->isSubmitted()){
	            if(!($id = $this->app->auth->verifyCredentials($f['email'],$f['password'])))
	                $f->displayError('email','Wrong Credentials');

	            
	            $user_model = $this->add('Model_User')->load($id);

	            if(!$user_model['is_verified'])
	                $f->displayError('email','Please Verified Your Account First');

	            if(!$user_model['is_active'])
	                $f->displayError('email','Please Activate Your Account First');

	            $this->api->auth->login($f['email']);
	            // user type is host then always redirect to host account or admin panel
	            if($user_model['type'] === 'host'){
	            	$this->app->redirect($this->app->url('account'));
	            }
	            // if reload page
	            if($this->reload == "parent"){
	            	$this->owner->js()->reload()->execute();
	            }elseif($this->reload_class){
	            	// $this->js(true,$this->js()->_selector('#comment_modalpopup')->modal('toggle'))->_selector('.'.$this->reload_class)->trigger('reload')->execute();
	            	$this->reload_class->js(true,$this->js()->_selector('#comment_modalpopup')->modal('toggle'))->reload(['status'=>'loginSuccess'])->execute();
	            }else{
	            	$this->api->redirect($this->api->url($this->app->recall('next_url')?:'index'))->execute();
	            }
	        }
			$facebook_controller = $this->add('Controller_Facebook',['hfrom'=>$_GET['hfrom']]);
			$url = $facebook_controller->getLoginUrl();
			$this->template->trySet('facebook_login_url',$url);

			if($facebook_controller->user instanceof Model_User){
				$this->app->auth->model->load($facebook_controller->user->id);
			}
			
			$google_controller = $this->add('Controller_Google',['hfrom'=>$_GET['hfrom']]);
			$url = $google_controller->getLoginUrl();
			$this->template->trySet('google_login_url',$url);

			if($google_controller->user instanceof Model_User){
				$this->app->auth->model->load($google_controller->user->id);
			}

			if($this->app->auth->model->loaded()){
				$this->template->trySetHtml('loginsuccess','<div class="container" style="padding:10px;background-color:green;color:white;font-size:16px;width:500px;">Login successfully redirecting please wait ...</div>');
				
				$next_url = $this->app->recall('next_url')?:"index";
				$this->app->redirect($this->app->url($next_url,['city'=>'udaipur']));

			}
		}


	}

	function defaultTemplate(){
		return array('view/login');
	}
}