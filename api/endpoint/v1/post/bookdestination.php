<?php

class endpoint_v1_post_bookdestination extends HungryREST {
    public $model_class = 'DestinationEnquiry';
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
        $m=$this->model;
        if($m->loaded()){
            if(!$this->allow_edit)throw $this->exception('Editing is not allowed');
            $data=$this->_input($data, $this->allow_edit);
        }else{
            if(!$this->allow_add)throw $this->exception('Adding is not allowed');
            $data=$this->_input($data, $this->allow_add);
        }

        $this->validateParam($data);
        if(!$this->api->auth->model->id)
            return json_encode(array('status'=>'failed','message'=>"authentication failed"));
        
        try{
            // $this->api->db->beginTransaction();
            $m->set($data);
            $m['user_id'] = $this->api->auth->model->id;
            $m->save();
           //$m->sendEnquiryToHungry();
            // $this->api->db->commit();
        }catch(\Exception_StopInit $e){

        }catch(\Exception $e){
            // $this->api->db->rollback();
            if($m->loaded())
                $m->delete();

            $msg = $e->getMessage();
            if(!$msg)
                $msg = 'if you not receive booking email or sms. please contact to respective destinaton or hungrydunia';

            return json_encode(array(
                            'status'=>"failed",
                            'message'=>$msg
                        ));
        }
         
        return json_encode(array(
                            'status'=>"success",
                            'message'=>"we have received your booking request. our representative will contact and confirm your booking"
                        ));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = ['name','adult','child','email','mobile','created_at','created_time','destination_id','package_id','occasion_id','total_budget','remark'];

  //      foreach ($required_param as $param) {
//            if(!array_key_exists($param, $data)){
             //   echo "Param Error 1001";
           //     exit;
         //   }
       // }
    }

}