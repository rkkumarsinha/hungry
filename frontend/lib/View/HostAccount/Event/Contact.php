<?php

class View_HostAccount_Event_Contact extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_event = $this->app->listmodel;

		$sticker_form = $this->add('Form',null,'pull_push_form',['form/stacked']);
		$sticker_form->addField('Number','quantity')->validateNotNull(true);
		$sticker_form->addSubmit("Pull Push Sticker");
		if($sticker_form->isSubmitted()){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Pull Push Sticker Request From ".$host_event['name'];
			$notification['from_id'] = $host_event->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "pull push sticker";
			$notification['status'] = "pending";
			$notification['value'] = $sticker_form['quantity'];

			$notification->save();
			$sticker_form->js(null,$sticker_form->js()->reload())->univ()->successMessage('Request Send Successfully')->execute();
		}

		$signature_form = $this->add('Form',null,'table_reservation_form',['form/stacked']);
		$signature_form->addField('Number','quantity')->validateNotNull(true);
		$signature_form->addSubmit("Table Reservation Signature");
		if($signature_form->isSubmitted()){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Table Reservation Signature Request From ".$host_event['name'];
			$notification['from_id'] = $host_event->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "table reservation signature";
			$notification['status'] = "pending";
			$notification['value'] = $signature_form['quantity'];
			$notification->save();
			$signature_form->js(null,$signature_form->js()->reload())->univ()->successMessage('Request Send Successfully')->execute();
		}

		$app_form = $this->add('Form',null,'android_app_form',['form/stacked']);
		$app_form->addSubmit("Android App");
		if($app_form->isSubmitted()){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Android App Request From ".$host_event['name'];
			$notification['from_id'] = $host_event->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "android app";
			$notification['status'] = "pending";
			$notification->save();
			$app_form->js(null,$app_form->js()->reload())->univ()->successMessage('Request Send Successfully')->execute();
		}


		$website_form = $this->add('Form',null,'website_form',['form/stacked']);
		$website_form->addSubmit("Website");
		if($website_form->isSubmitted()){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Website Request From ".$host_event['name'];
			$notification['from_id'] = $host_event->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "website";
			$notification['status'] = "pending";
			$notification->save();
			$app_form->js(null,$app_form->js()->reload())->univ()->successMessage('Request Send Successfully')->execute();
		}

		$form = $this->add('Form',null,'enquiry_form');
		$form->addField('line','subject');
		$form->addField('text','description');
		$form->addSubmit('Send');

		if($form->submitted()){
			$form->js(null,$form->js()->reload())->univ()->successMessage('Request Send')->execute();
		}

	}

	function DefaultTemplate(){
		return ['view/contacttohungry'];
	}

}