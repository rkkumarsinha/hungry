<?php

class endpoint_v1_post_registration extends Endpoint_REST{
    public $model_class = 'User';
    public $allow_list=false;
    public $allow_list_one=false;
    public $allow_add=true;
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
        return true;
    }
    /**
     * Because it's not really defined which of the two is used for updating
     * the resource, Agile Toolkit will support put_post identically. Both
     * of the requests are idempotent.
     *
     * As you extend this class and redefine methods, you should properly
     * use POST or PUT. See http://stackoverflow.com/a/2691891/204819
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
    */

    function put_post($data){
        $this->validateParam($data);

        $m = $this->model;

        if($m->loaded()){
            if(!$this->allow_edit)throw $this->exception('Editing is not allowed');
            $data=$this->_input($data, $this->allow_edit);
        }else{
            if(!$this->allow_add)throw $this->exception('Adding is not allowed');
            $data=$this->_input($data, $this->allow_add);
        }
        
    
        if($data['access_token'] and in_array($data['social'], ['Facebook','Google']) ){
            if(!in_array($data['social'], ['Facebook','Google']))
                return json_encode(
                            array(
                                'status'=>"failed",
                                'message'=>"login with ".$data['social']." social media not allowed"
                            ));
            if($data['referral_code']){
                $ref_user = $this->add('Model_User')->addCondition('referral_code',$data['referral_code'])->tryLoadAny();
                unset($data['referral_code']);
                if(!$ref_user->loaded()){
                    return json_encode(
                                array(
                                    "status"=>"failed",
                                    'message'=>"wrong referral code, try with correct referral or leave it blank",
                                    // "user_id"=>$old_user['id'],
                                    // "access_token"=>$access_model['social_access_token'],
                                    // "social"=>$access_model['social_app']
                                ));       
                }
            }

            $controller = $this->add('Controller_'.$data['social'],array('hfrom'=>$data['social'],'access_token'=>$data['access_token'],'call_loginfunction'=>false,'social_content'=>$data['social_content'],'all_data'=>$data));
            $new_user_model = $controller->loginStatus($data['access_token']);

            if(!$new_user_model){
                return json_encode(
                            array(
                                'status'=>"failed",
                                'message'=>"access token is not valid"
                            ));
            }

            if(!$new_user_model['dob'])
                $new_user_model['dob'] = $data['dob'];
            if(!$new_user_model['mobile'])
                $new_user_model['mobile'] = $data['mobile'];

            if(isset($ref_user))
                $new_user_model['referral_user_id'] = $ref_user->id;

            $new_user_model->save();
            $m = $this->model = $new_user_model;
            $access_token = $new_user_model->getAccessModel($data['social']);
            
         }else{

            //check email is valid
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                return json_encode(array('status'=>"failed",'message'=>"email id is not valid"));
            }
            
            // check mobile is valid
            preg_match_all("/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/", $data['mobile'], $matches);
            if(!count($matches[0]))
                return json_encode(array('status'=>'failed','message'=>"mobile number is not valid"));
            
            if($old_user = $m->checkEmailMobileExit($data['email'],$data['mobile'])){
                // return user id and access token
                $access_model = $old_user->getAccessModel("HungryDunia");
                if($access_model and ($access_model['access_token_expire_on'] and ($this->api->today >= $old_user['access_token_expire_on'])) ){
                    return json_encode(
                                array(
                                    "status"=>"failed",
                                    'message'=>"access token expire, login again"
                                ));                    
                }

                return json_encode(
                                array(
                                    "status"=>"failed",
                                    'message'=>"your email or mobile number is already registered, please try with different email id and mobile number",
                                    // "user_id"=>$old_user['id'],
                                    // "access_token"=>$access_model['social_access_token'],
                                    // "social"=>$access_model['social_app']
                                ));
            }

            if($data['referral_code']){
                $ref_user = $this->add('Model_User')->addCondition('referral_code',$data['referral_code'])->tryLoadAny();
                if($ref_user->loaded()){
                    $m['referral_user_id'] = $ref_user->id;
                    unset($data['referral_code']);
                }else{
                    return json_encode(
                                array(
                                    "status"=>"failed",
                                    'message'=>"wrong referral code, try with correct referral or leave it blank",
                                    // "user_id"=>$old_user['id'],
                                    // "access_token"=>$access_model['social_access_token'],
                                    // "social"=>$access_model['social_app']
                                ));       
                }
            }
            
            $m->set($data);
            $m['is_active'] = 1;
            $m['type'] = 'user';
            
            $md5_access_token = md5(uniqid($m['email']."-".$m['created_at'], true));
            // saved encrypted code
            $auth=$this->add('Auth');
            $auth->usePasswordEncryption();
            $m["password"] = $auth->encryptPassword($data["password"],$data['email']);
            $m->save();
            
            $access_token = $this->add('Model_AccessToken');
            $access_token['user_id'] = $m->id;
            $access_token['social_app'] = "HungryDunia";
            $access_token['social_access_token'] = $md5_access_token;
            $access_token->save();
        }

        // sending email after user saved into database        
        try{
            if($data['social'] == "HungryDunia"){
                $m->sendOTP();
                $m->sendAppRegistrationWelcomeMail();
            }

            return json_encode(array(
                                'status'=>"success",
                                "message"=>"your account has been created successfully",
                                "user_id"=>$m['id'],
                                "social"=>$access_token['social_app'],
                                "access_token"=>$access_token['social_access_token'],
                                "referral_code"=>$m['referral_code']
                            ));
        }catch(\Exception $e){
            return json_encode(array('status'=>'failed','message'=>"Internet/Server Problem"));
        }
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