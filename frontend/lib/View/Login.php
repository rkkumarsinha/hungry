<?php

class View_Login extends View{
	public $reload;
	public $reload_class;
	public $reload_page = false;
	function init(){
		parent::init();
		
		if($this->app->page == "bookticket")
			$this->app->memorize('event_slug',$_GET['slug']);
		
		if($this->api->auth->model->id){
			$container = $this->add("View")->addClass('container')->setStyle(['width'=>'100%','margin-top'=>'20px']);
            $container->add('View_Info',null)->set('already logged in');
            $container->add('Button',null)->set('Logout')->addClass('atk-swatch-red')->js('click')->redirect($this->api->url('logout'));
			$this->template->tryDel("login_wrapper");
            return;

	    }else{
	        //Redirect Page
	  //       if(!session_id()) {
			//     session_start();
			// }
	        if($status = $this->api->recall('from')){
	        	$info = $this->add('View_Info');
	        	if($status=="forgotpassword"){
	        		$info->set('Your Password Changed Successfully');
	        	}elseif($status=="verification"){
	        		$info->set('You Account Activated Successfully');
	        	}elseif($status=="verificationhost"){
	        		$info->set('you email is verified, you can login once your account is activated by HungryDunia');
	        	}
	        	$this->api->stickyForget('from');
	        }

	        if($this->api->recall('next_url')){
	            $redirect_url = array('next_url'=>$this->api->recall('next_url'));
	        }
	        
			$facebook_controller = $this->add('Controller_Facebook',['hfrom'=>$_GET['hfrom'],'isWebsiteCheck'=>true]);

			$fb_btn = $this->add('Button',null,'facebook_btn');
			$fb_btn->set('Sign In with Facebook');
			$fb_btn->addClass('facebook');
			$fb_btn->setIcon('facebook');
			if($fb_btn->isClicked()){
				$url = $facebook_controller->getLoginUrl();
				$this->app->redirect($url);
			}
			// $fb_btn->js('click')->univ()->redirect($url);
			// $this->template->trySet('facebook_login_url',$url);

			if($facebook_controller->user instanceof Model_User){
				// session_write_close();
				$user_model = $this->add('Model_User')->load($facebook_controller->user->id);
				// $this->api->auth->login($user_model);
				if(!$user_model['email'] OR !$user_model['mobile']){
					// $this->app->memorize('facebook_email_not_found',$facebook_controller->user->id);
					$this->app->redirect($this->app->url('validate',['email_not_found'=>$facebook_controller->user->id."_"]));
				}
				else{
					$this->api->auth->loginById($user_model->id);
					// $this->api->auth->login($user_model);
					if($slug = $this->app->recall('event_slug')){
						$this->app->forget('event_slug');
						$this->app->redirect($this->app->url('bookticket',['slug'=>$slug]));
					}
          			$this->api->redirect($this->api->url('account'));
				}
			}
			// $_SESSION["FBRLH_persist"] = $_SESSION["FBRLH_state"];
			
			$google_controller = $this->add('Controller_Google',['hfrom'=>$_GET['hfrom']]);

			$g_btn = $this->add('Button',null,'google_btn');
			$g_btn->set('Sign In with Google');
			$g_btn->addClass('google');
			$g_btn->setIcon('fa fa-google-plus',true);

			if($g_btn->isClicked()){
				$url = $google_controller->getLoginUrl();
				$this->app->redirect($url);
			}

			if($google_controller->user instanceof Model_User){				
				$user_model = $this->add('Model_User')->load($google_controller->user->id);
				
				if(!$user_model['email'] OR !$user_model['mobile']){
					$this->app->redirect($this->app->url('validate',['email_not_found'=>$google_controller->user->id."_"]));
				}else{
					$this->api->auth->loginById($user_model->id);
					if($slug = $this->app->recall('event_slug')){
						$this->app->forget('event_slug');
						$this->app->redirect($this->app->url('bookticket',['slug'=>$slug]));
					}
          			$this->api->redirect($this->api->url('account'));
				}
				// $this->app->auth->model->load($google_controller->user->id);
			}

			if($this->app->auth->model->loaded()){
				$this->template->trySetHtml('loginsuccess','<div class="container" style="padding:10px;background-color:green;color:white;font-size:16px;width:500px;">Login successfully redirecting please wait ...</div>');
				$next_url = $this->app->recall('next_url')?:"index";
				$this->app->redirect($this->app->url($next_url,['city'=>'udaipur']));
			}

			$f = $this->add('Form',null,'generalloginform',['form/stacked']);
	        $f->addField('email')->validateNotNull()->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)')->setAttr('PlaceHolder','enter your email');
	        $f->addField('password','password')->validateNotNull()->setAttr('PlaceHolder',"enter your password");
	        $f->addSubmit('Login')->addClass('btn-block');
			

			$r_btn = $this->add('Button',null,'new_register');
			$r_btn->set('Sign Up');
			$r_btn->addClass('atk-swatch-orange btn-block');
			$r_btn->setIcon('fa fa-sign-in',true);
			$r_btn->js('click')->univ()->redirect($this->app->url('register'));

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

	            if(!$user_model['is_active']){
	                	$f->displayError('email','Please Activate Your Account First');
	            }

	            $this->app->auth->loginById($id);
	            // user type is host then always redirect to host account or admin panel
	            if($user_model['type'] === 'host'){
	            	$this->app->redirect($this->app->url('account'));
	            }
	            
	            if($this->reload_page){
	            	$this->app->redirect($this->app->url());
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
		}

	}

	function defaultTemplate(){
		return array('view/login');
	}
}