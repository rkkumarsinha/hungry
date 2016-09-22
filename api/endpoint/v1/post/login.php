<?php

class endpoint_v1_post_login extends HungryREST{
    public $model_class = 'User';
    public $allow_list=false;
    public $allow_list_one=true;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){
    	parent::init();

    }

    function get(){
        return "you are not allow to access";
    }

    function _model(){
        return parent::_model();
    }

    function authenticate(){
        $headers=array();
        foreach (getallheaders() as $name => $value) {
            $headers[$name] = $value;
        }

        $user_name  = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        $headers = getallheaders();
        $user_model = $this->add('Model_User')
                        ->addCondition('is_active',true)
                        ->addCondition('is_blocked','<>',true)
                        ->addCondition('type','user')
                        ;

        $auth=$this->add('Auth');
        $auth->usePasswordEncryption();
        $auth->setModel($user_model);

        $login_id = $auth->verifyCredentials($user_name,$password);
        if(!$login_id){
            echo "wrong credential";
            exit;
        }
        
        if($login_id){
            $verify_user_model = $this->add('Model_User')->load($login_id);
            if(!$verify_user_model['is_active'] or $verify_user_model['is_blocked'])
                return json_encode(array(
                            'status'=>"failed",
                            'message'=>'please activate your account first'
                        ));
            $this->api->auth->model = $verify_user_model;

            $access_token = $verify_user_model->getAccessModel("HungryDunia");
            
            return json_encode(array(
                    'status'=>"success",
                    "message"=>"your account has been login successfully",
                    "user_id"=>$verify_user_model['id'],
                    "social"=>"HungryDunia",
                    "access_token"=>$access_token['social_access_token']
                ));
        }

        return json_encode(array(
                            'status'=>"failed",
                            'message'=>'wrong credential'
                        ));
    }

    function put_post(){
        $access_token = $this->api->auth->model->getAccessModel("HungryDunia");
        return json_encode(array(
                    'status'=>"success",
                    "message"=>"your account has been login successfully",
                    "user_id"=>$this->api->auth->model['id'],
                    "social"=>$access_token['social_app'],
                    "access_token"=>$access_token['social_access_token'],
                    "user_name"=>$this->api->auth->model['name'],
                    "mobile"=>$this->api->auth->model['mobile']
                ));
    }

	function delete($data){
        return "you are not allow to access";
	}

    function validateParam($data){
        
        $required_param = ['name','email','created_at','dob','mobile','received_newsletter','social','social_content','password','referral_code'];
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param = $param Error 1001";
                exit;
            }
        }

                
    }

}