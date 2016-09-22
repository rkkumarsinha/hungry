<?php
/**
 * Implementation of RESTful endpoint for App_REST
 */
// @codingStandardsIgnoreStart because REST is acronym
class HungryREST extends Endpoint_REST{

	function init(){
		parent::init();
		
	}

	// Implementing token based authetication
    function authenticate(){
    	$headers = getallheaders();
    	$data = $_POST;

    	// load accesstoken 
    	// load user based on loaded accesstoken
    	// check loaded user id equal to header user id or social content equals to header social
		$access_token_model = $this->add('Model_AccessToken')->addCondition('social_access_token',$headers['Accesstoken'])->tryLoadany();
		
		if($access_token_model->loaded()){
			// first check accesstoken is expire or not
			if($access_token_model['access_token_expire_on'] and ($access_token_model['access_token_expire_on'] < $this->api->today)){
				return [
                        'status'=>"failed",
                        'message'=>"access token expired"
                    ];
			}

			$user_model = $this->add('Model_User')->load($access_token_model['user_id']);
			if($user_model->id != $headers['Userid'])
				return ['status'=>"failed",'message'=>"wrong credential"];
			
			if($access_token_model['social_app'] != $headers['Social'])
				return ['status'=>"failed",'message'=>"wrong credential"];
			
			if(!$user_model['is_active'] or $user_model['is_blocked'])
				return ['status'=>"failed",'message'=>"please activate your account."];

			$auth = $this->add('Auth');
			$auth->setModel($user_model);
			$this->api->auth->model = $user_model;

			return ['status'=>"success",'message'=>"login successfull"];
		}

		return ['status'=>"failed",'message'=>"wrong credential."];
    }

    function validateEmail($email){
    	//check email is valid
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        	return false;
        }

        return true;
    }

    function validateInt(){

    }

    function validateMobileNumber($mobile){
    	// check mobile is valid
        preg_match_all("/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/", $mobile, $matches);
        if(!count($matches[0]))
            return false;

        return true;
    }

    function validateDate(){
    	
    }

    function validateTime(){
    	
    }

}
