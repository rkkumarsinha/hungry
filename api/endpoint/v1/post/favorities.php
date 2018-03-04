<?php

class endpoint_v1_post_favorities extends HungryREST {
    public $model_class = 'Favorities';
    public $allow_list=false;
    public $allow_list_one=false;
    public $allow_add=true;
    public $allow_edit=false;
    public $allow_delete=true;

    function get(){
        return "you are not allow to access";
    }

    function _model(){
        return parent::_model();
    }

    function authenticate(){
        $data = parent::authenticate();
        if($data['status'] === "success")
            return true;

        echo json_encode($data);
        exit;
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
        $m = $this->model;
        if($m->loaded()){
            if(!$this->allow_edit)throw $this->exception('Editing is not allowed');
            $data=$this->_input($data, $this->allow_edit);
        }else{
            if(!$this->allow_add)throw $this->exception('Adding is not allowed');
            $data=$this->_input($data, $this->allow_add);
        }
        
        $this->validateParam($data);

        date_default_timezone_set("Asia/Calcutta");
        $m->addCondition('user_id',$this->api->auth->model->id);
        $m->addCondition('restaurant_id',$data['restaurant_id']);
        $m->tryLoadAny();
        $m['created_at'] = date('Y-m-d H:i:s');
        $m->save();
        
        return json_encode(
                    array(
                    'status'=>"success",
                    "message"=>"favorities added successfully"
                ));
	}

	function delete($data){
        return "not allow to delete";

        $m = $this->model;
        $m->addCondition('user_id',$this->api->auth->model->id);
        $m->addCondition('restaurant_id',$data['restaurant_id']);
        $m->tryLoadAny();
        
        if($m->loaded()){
            $m->delete();
            return json_encode(
                    array(
                    'status'=>"success",
                    "message"=>"favorities delete successfully"
                ));
        }
        return json_encode(
                    array(
                    'status'=>"failed",
                    "message"=>"could not delete"
                ));
	}

    function validateParam($data){
        $required_param = ['restaurant_id'];

        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }
        }
        // check restaurant exist or not
        if($data['restaurant_id']){
            $rest_check = $this->add('Model_Restaurant')->addCondition('id',$data['restaurant_id'])->tryLoadAny();
            if(!$rest_check->loaded()){
                echo "no restaurant record found";
                exit;
            }
        }

    }
}
