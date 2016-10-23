<?php

class page_contact extends Page{

    function init(){
        parent::init();
		
		$this->add('View_DownloadApp',null,'downloadapp');	

		$form = $this->add('Form',null,'contact_form',['form/stacked']);
		$form->setLayout(['page/contact','contact_form']);
		$form->addField('name')->validateNotNull(true);
		$form->addField('subject')->validateNotNull(true);
		$form->addField('email')->validateNotNull(true)->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');
		$form->addField('number','mobile_no')->validateNotNull(true);
		$form->addField('text','message')->validateNotNull(true);

		if($form->isSubmitted()){
			if(strlen($form['mobile_no']) != 10)
				$form->error('mobile_no','must be 10 number digit only');
			if(!in_array(substr(trim($form['mobile_no']),0,1) , [7,8,9]))
				$form->error('mobile_no','must start with 7,8 or 9');

			$enquiry = $this->add('Model_Enquiry');

			if(isset($this->app->auth->model))
				$enquiry['user_id'] = $this->app->auth->model->id;
			$enquiry['name'] = $form['name'];
			$enquiry['subject'] = $form['subject'];
			$enquiry['email'] = $form['email'];
			$enquiry['mobile_no'] = $form['mobile_no'];
			$enquiry['subject'] = $form['subject'];
			$enquiry['message'] = $form['message'];
			$enquiry->save();

			$form->js(null,$form->js()->reload())->univ()->successMessage('Enquiry submitted successfully')->execute();
		}
    }

    function defaultTemplate(){
    	return ['page/contact'];
    }
}