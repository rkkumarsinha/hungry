<?php

class endpoint_v1_post_forgetotp extends Endpoint_REST{
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

        //check email is valid 
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            return json_encode(array('status'=>"provide valid email id"));
        }
        
        // check mobile is valid
        preg_match_all("/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/", $data['mobile'], $matches);
        if(!count($matches[0]))
            return json_encode(array('status'=>"provide valid mobile number"));

        $user_model = $this->add('Model_User');
        $user_model->addCondition('email',$data['email']);
        $user_model->addCondition('mobile',$data['mobile']);
        $user_model->tryLoadAny();
        if(!$user_model->loaded())
            return json_encode(array('status'=>'failed','message'=>"wrong credentials"));

        try{
            $user_model->sendOTP();
            return json_encode(array(
                                'status'=>"success",
                                'message'=>"otp send to your registered email or mobile number"
                            ));
        }catch(\Exception $e){
            return json_encode(array('status'=>'failed'));
        }
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        
        $required_param = ['email','mobile'];
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }

        }
    }

}