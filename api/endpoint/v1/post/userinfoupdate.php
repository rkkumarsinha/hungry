<?php

class endpoint_v1_post_userinfoupdate extends HungryREST {
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
        // parent::get();
    }

    function _model(){
        return parent::_model();
    }

    function authenticate(){
        $data = parent::authenticate();
        if($data['status'] === "success")
            return true;

        echo json_encode($data);
        return false;
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
        $m=$this->model;
        if($m->loaded()){
            if(!$this->allow_edit)throw $this->exception('Editing is not allowed');
            $data=$this->_input($data, $this->allow_edit);
        }else{
            if(!$this->allow_add)throw $this->exception('Adding is not allowed');
            $data=$this->_input($data, $this->allow_add);
        }

        $this->validateParam($data);

        $this->model->addCondition('id',$this->api->auth->model->id)->tryLoadAny();
        if(!$this->model->loaded())
            return json_encode(array('status'=>'failed','message'=>"authebtication failed"));
        
        $old_user = $this->add('Model_User')
                    ->addCondition('mobile',$data['mobile'])
                    ->addCondition('id','<>',$this->model->id)
                    ->tryLoadAny();

        if($old_user->loaded()){
            return json_encode(array('status'=>'failed','message'=>"mobile number is already exist"));
        }

        $this->model['name'] = $data['name'];
        $this->model['dob'] = $data['dob'];
        $this->model['mobile'] = $data['mobile'];
        $this->model->save();
        
        return json_encode(array('status'=>'success','message'=>"your profile has been updated."));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = ['name','dob','mobile'];
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }
        }

    }

}