<?php

class View_Account_Profile extends View{
	
	function init(){
		parent::init();

		if(!$this->app->auth->model->id){
			$this->app->redirect($this->app->url('signin'));
			exit;
		}

		// Profile Image Url
		$image_view = $this->add('View',null,'profile_pic',['view\account\profile','profile_pic']);
		$user = $this->add('Model_User')->load($this->app->auth->model->id);
		$image_view->template->trySet('profile_image',$user['profile_image_url']);

		$profile = $this->add('Form',null,'profile_form',['form/minimal']);
		$profile->setModel($user,['image','image_id']);
		$profile->addSubmit('Update Image');

		if($profile->isSubmitted()){
			
			$profile->save();

			$acces = $this->add('Model_AccessToken')
					->addCondition('user_id',$user->id)
					->addCondition('social_app',"HungryDunia")
					->addCondition('social_app',"HungryDunia")
					->tryLoadAny();
			$acces['profile_image_url'] = $profile->model['image'];
			$acces->save();

			$profile->js()->univ()->successMessage("profile picture updated")->execute();

		}

		// Personal Info Form
		$form = $this->add('Form',null,'personal_form');
		$form->setLayout(['view\account\profile','personal_form']);
		$user = $this->add('Model_User')->load($this->app->auth->model->id);
		$form->setModel($user,['name','email','mobile','dob','country_id','state_id','city_id','street','zip','address','extra_info']);
		$submit_btn = $form->add('Button')->set('Save Changes')->addClass('btn btn-large btn-default');
		$submit_btn->js('click',$form->js()->submit());
		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage("Saved Successfully")->execute();
		}

		// Change Password
		$pass_form = $this->add('Form',null,'chnage_password_form');
		$pass_form->setLayout(['view\account\profile','chnage_password_form']);
		$pass_form->addField('password','current_password')->validateNotNull();
		$pass_form->addField('password','new_password')->validateNotNull();
		$pass_form->addField('password','confirm_password')->validateNotNull();
		$pass_btn = $pass_form->add('Button')->set('Save Changes')->addClass('btn btn-default');
		$pass_btn->js('click',$pass_form->js()->submit());
		if($pass_form->isSubmitted()){
			if($pass_form['new_password'] != $pass_form['confirm_password'])
				$pass_form->error('new_password','password must same');

			if(!($id = $this->app->auth->verifyCredentials($this->app->auth->model['email'],$pass_form['current_password'])))
	            $pass_form->displayError('current_password','Wrong Credentials');

	        $user = $this->add('Model_User')->load($this->app->auth->model->id);
	        $user['password'] = $pass_form['new_password'];
	        $user->save();

	        $pass_form->app->redirect($this->app->url('logout'));
		}
	}

	function render(){

		parent::render();
	}

	function defaultTemplate(){
		return ['view\account\profile'];
	}

}