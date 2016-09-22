<?php

class endpoint_v1_post_useractivation extends Endpoint_REST{
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

    // function authenticate(){
    //     return true;
    //     $data = parent::authenticate();
    //     if($data['status'] === "success")
    //         return true;

    //     echo json_encode($data);
    //     return false;
    // }
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
        
        if(!$m->checkOPTExpire($data['otp'],$data['email'])){
            return json_encode(
                            array(
                                'status'=>"failed",
                                'message'=>"your OTP has been expired, try with other otp"
                            ));
        }

        // try{
            $m->markVerify($data['email'],$data['otp']);
            return json_encode(array(
                                "status"=>"success",
                                'message'=>"welcome to hungrydunia ! Your account has been successfully verified"
                            ));
        // }catch(\Exception $e){
        //     return json_encode(array('status'=>'failed'));
        // }
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        
        $required_param = ['email','otp'];
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }

        }
    }

}