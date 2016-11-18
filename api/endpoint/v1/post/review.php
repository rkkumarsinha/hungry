<?php

class endpoint_v1_post_review extends HungryREST {
    public $model_class = 'Review';
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
        
        $this->validateParam($data);

        date_default_timezone_set("Asia/Calcutta");
        $m->set($data);
        $m['user_id'] = $this->api->auth->model->id;
        $m['comment'] = $data['content'];
        //$m['created_at'] = date('Y-m-d',strtotime($data['date']));
        //$m['created_time'] = $data['time'];
        $m['created_at'] = date('Y-m-d');
        $m['created_time'] = date('H:i:s');
        $m->save();
        
        return json_encode(
                    array(
                    'status'=>"success",
                    "message"=>"your review has been successfully submitted and being processed for verification"
                ));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        if($data['restaurant_id']){
            $required_param = ['restaurant_id','title','content','rating','date','time'];
        }
        
        if($data['destination_id'])
            $required_param = ['title','content','rating','date','time','destination_id'];

        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001";
                exit;
            }
        }

        if($data['restaurant_id'] and $data['destination_id']){
            echo "either restaurant id or destination id";
            exit;
        }

        //check restaurant exist or not
        if($data['restaurant_id']){

            $rest_check = $this->add('Model_Restaurant')->addCondition('id',$data['restaurant_id'])->tryLoadAny();

            if(!$rest_check->loaded()){
                echo "no record found";
                exit;
            }
        }

    }

}
