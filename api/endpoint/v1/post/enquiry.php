<?php

class endpoint_v1_post_enquiry extends HungryREST {
    public $model_class = 'Enquiry';
    public $allow_list=false;
    public $allow_list_one=false;
    public $allow_add=true;
    public $allow_edit=false;
    public $allow_delete=false;

    function init(){
    	parent::init();
    	// throw new \Exception(print_r($_GET));        
    }

    function get(){
        return "you are not allow to access";
        // parent::get();
    }

    function _model(){
        return parent::_model();
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
        $m = $this->model;
        if($m->loaded()){
            if(!$this->allow_edit)throw $this->exception('Editing is not allowed');
            $data=$this->_input($data, $this->allow_edit);
        }else{
            if(!$this->allow_add)throw $this->exception('Adding is not allowed');
            $data=$this->_input($data, $this->allow_add);
        }
        
        // $this->validateParam($data);
        $m->set($data);
        $m['user_id'] = $this->api->auth->model->id;
        $m->save();
        
        return json_encode(
                    array(
                    'status'=>"success",
                    "message"=>"Thank you! we have successfully received your enquiry, our representative contact you shortly."
                ));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = ['name','email','mobile','subject','message','location'];
                
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }

        }

        if(!$this->validateString($data['name'])){
            echo "Params error 1001";
        }

    }

}