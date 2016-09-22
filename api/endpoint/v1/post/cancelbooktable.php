<?php

class endpoint_v1_post_cancelbooktable extends HungryREST {
    public $model_class = 'ReservedTable';
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
        
        $booked_table = $this->add('Model_ReservedTable')->addCondition('restaurant_id',$data['restaurant_id'])
                                    ->addCondition('user_id',$this->api->auth->model->id)
                                    ->addCondition('booking_id',$data['booking_id'])
                                    ->tryLoadany();
        if(!$booked_table->loaded()){
            return json_encode(array(
                            'status'=>"failed",
                            'message'=>"table booking not found"
                        ));
        }


        try{
            // $this->api->db->beginTransaction();
            $m->load($booked_table->id);
            $m['status'] = "canceled";
            $m['canceled_by'] = "user";
            $m['CancledReason'] = $data['reason'];
            $m->save();
            // $this->api->db->commit();
        }catch(\Exception_StopInit $e){

        }catch(\Exception $e){
            // $this->api->db->rollback();
            if($m->loaded())
                $m->delete();

            $msg = $e->getMessage();
            return json_encode(array(
                            'status'=>"failed",
                            'message'=>$msg
                        ));
        }

        return json_encode(array(
                            'status'=>"success",
                            'message'=>'table booking canceled processed',
                            'booking_number'=>$m['booking_id'],
                            'booking_status'=>$m['status']
                        ));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = ['booking_id','restaurant_id'];
   
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
               echo json_encode(array('status'=>'failed','message'=>"Param Error 1001"));
                exit;
            }
        }

        if(!$data['restaurant_id']){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"param error restaurant must pass",
                        ));
            exit;
        }

        $rest = $this->add('Model_Restaurant')->tryLoad($data['restaurant_id']);
        if(!$rest->loaded()){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"restaurant not found",
                        ));
            exit;
        }

        if(!$data['reason']){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"specify cancellation reason",
                        ));
            exit;   
        }

    }

}