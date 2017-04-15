<?php

class View_DownloadApp extends \View{
	function init(){
		parent::init();

		$a_view = $this->add('View')->setElement('a')->setAttr('href','https://play.google.com/store/apps/details?id=com.hungrydunia.app&hl=en')->setAttr('targer','_blank');
		$img = $a_view->add('View')->setElement('img')->setAttr('src',$this->app->getConfig('absolute_url').'frontend/public/img/hungrydownloadtheapp.jpg')->setStyle('border-radius','5px')->addClass('social-box-wrapper');

		$form = $this->add('Form',null,null,['form/stacked'])->addClass('atk-box')->setStyle('background-color','transparent');
		$form->addField('Number','mobile_no')->validateNotNull(true);
		$form->addSubmit('Get Link');

		if($form->isSubmitted()){
			if(strlen(trim($form['mobile_no'])) != 10)
				$form->error('mobile_no','must 10 digit number');

			if(!in_array(substr(trim($form['mobile_no']),0,1) , [7,8,9]))
				$form->error('mobile_no','must start with 7,8 or 9');

			// DOWNLOADAPPLINK
			$sms_template = $this->add('Model_EmailTemplate');
			$sms_template->addCondition('name',"DOWNLOADAPPLINK")->tryLoadAny();

			if(!$sms_template->loaded())
				throw new \Exception("something wrong, sms template may be delete");
					
			if(!trim($sms_template['body']))
				throw new \Exception("sms template body missing");

			$body = $sms_template['body'];
			$outbox = $this->add('Model_Outbox');
			try{

				$sms_response = $outbox->sendSMS($form['mobile_no'],$body);
				if($sms_response != true){
					throw new \Exception($sms_response);
				}
				$subs = $this->add('Model_Subscriber');
				$subs['mobile_no'] = $form['mobile_no'];
				$subs->save();

				$outbox->createNew("Download app link",$form['mobile'],"SMS",$body,"SMS","download app link on ".$form['mobile_no'],$subs->id);
				$form->js(null,$form->js()->reload())->univ()->successMessage('Download Link Send Successfully')->execute();
			}catch(Exception $e){
				$form->js()->univ()->errorMessage('sending error')->execute();
			}

		}
	}

	// function defaultTemplate(){
	// 	return ['view/downloadapp'];
	// }
}