<?php

class View_HostAccount_Event_ChangePassword extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_event = $this->app->listmodel;

		$host = $this->add('Model_User')
					->addCondition('type','host')
					->addCondition('id',$host_event['user_id'])
					->tryLoadAny();
		if(!$host->loaded()){
			$this->add('View_Error')->set('Host User Not found');
			return;
		} 

		$view = $this->add('View')->addClass('promo-box host-account-change-password');
		$pass_form = $view->add('Form');
		$pass_form->setLayout(['view\account\changepassword']);
		$pass_form->addField('password','current_password')->validateNotNull();
		$pass_form->addField('password','new_password')->validateNotNull();
		$pass_form->addField('password','confirm_password')->validateNotNull();
		$pass_btn = $pass_form->add('Button')->set('Change Password')->addClass('btn btn-default');
		$pass_btn->js('click',$pass_form->js()->submit());
		if($pass_form->isSubmitted()){
			if($pass_form['new_password'] != $pass_form['confirm_password'])
				$pass_form->error('new_password','password must same');

			if(!($id = $this->app->auth->verifyCredentials($this->app->auth->model['email'],$pass_form['current_password'])))
	            $pass_form->displayError('current_password','Wrong Credentials');

	        // $user = $this->add('Model_User')->load($this->app->auth->model->id);

	        $host['password'] = $pass_form['new_password'];
	        $host->save();
	        $pass_form->app->redirect($this->app->url('logout'));
		}

	}
}