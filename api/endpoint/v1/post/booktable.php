<?php

class endpoint_v1_post_booktable extends HungryREST {
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

        try{
            // $this->api->db->beginTransaction();

            $m->set($data);
            if(isset($data['offer_id']))
                $m['restoffer_id'] = $data['offer_id'];
            
            $m['user_id'] = $this->api->auth->model->id;
            $m['book_table_for'] = $data['name'];
            $m['no_of_adult'] = $data['adult'];
            $m['no_of_child'] = $data['child'];
            $m['booking_date'] = $data['date'];
            $m['booking_time'] = $data['time'];

            if($data['discount_id']){
                $rest = $this->add('Model_Restaurant')->tryLoad($data['restaurant_id']);
                $m['discount_offer_value'] = $rest['discount_percentage_to_be_given'];
            }
            if($data['offer_id']){
                $offer_model = $this->add('Model_RestaurantOffer')->load($data['offer_id']);
                $m['discount_offer_value'] = $offer_model['name']." ".$offer_model['sub_name']." ".$offer_model['detail'];
            }

            $m->save();
            $m->sendEnquiryEmailToHost();
            $m->sendProcessingSMS();
           // $m->sendReservedTable();
            // $this->api->db->commit();
        }catch(\Exception_StopInit $e){

        }catch(\Exception $e){
            // $this->api->db->rollback();
            if($m->loaded())
                $m->delete();

            $msg = $e->getMessage();
            if(!$msg)
                $msg = 'either email, phone is wrong or internet or server failure';

            return json_encode(array(
                            'status'=>"failed",
                            'message'=>$msg
                        ));
        }

        return json_encode(array(
                            'status'=>"success",
                            'message'=>'your reservation is beign processed. you will shortly receive confirmation mail or sms.',
                            'booking_number'=>$m['booking_id'],
                            'booking_status'=>$m['status']
                        ));
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = ['name','adult','child','email','mobile','date','time','discount_id','offer_id','restaurant_id'];
                
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
               echo json_encode(array('status'=>'failed','message'=>"Param Error 1001"));
                exit;
            }

        }

        if(!$this->validateMobileNumber($data['mobile'])){
            echo json_encode(array('status'=>'failed','message'=>"mobile number is not valid"));
                exit;
        }

        if(!$this->validateEmail($data['email'])){
            echo json_encode(array('status'=>'failed','message'=>"email is not valid"));
            exit;
        }


        if($this->api->today > $data['date'] ){
            echo json_encode(array('status'=>"failed",'message'=>'booking not available for past date'));
            exit;
        }

        if(!$data['restaurant_id']){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"param error restaurant must pass",
                        ));
            exit;
        }

        if( $data['offer_id'] && $data['discount_id'] ){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"select either Discount or Offer"
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

        if($data['offer_id']){
            $om = $this->add('Model_RestaurantOffer')->tryLoad($data['offer_id']);
            if(!$om->loaded()){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"offer not found"
                        ));
                exit;
            }

            if($om['is_active'] != 1){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"offer not found"
                        ));
                exit;
            }
            
            if($om['restaurant_id'] != $rest->id){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"offer not found"
                        ));
                exit;
            }

            $appli_offer = $this->add('Model_RestaurantOffer')->addCondition('restaurant_id',$rest->id)->addCondition('id',$om->id);
            if($appli_offer->count()->getOne() != 1){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"offer is not applicable on this restaurant"
                        ));
                exit;   
            }
        }

        if($data['discount_id']){
            $dm = $this->add('Model_Discount')->tryLoad($data['discount_id']);
            if(!$dm->loaded()){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"discount not found"
                        ));
                exit;
            }

            if($data['discount_id'] != $rest['discount_id']){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"discount is not applicable on selected restaurant"
                        ));
                exit;
            }
        }

        $table_count = $this->add('Model_ReservedTable')
                    ->addCondition('user_id',$this->api->auth->model->id)
                    ->addCondition('created_at',$this->api->today)
                    ->count()->getOne();
        if($table_count >= 3){
            return json_encode(array('status'=>"Failed",'message'=>'today limit exceeded, please try after 24 Hours'));
            exit;
        }

        // check for today booking already
        $old = $this->add('Model_ReservedTable')
                ->addCondition('user_id',$this->api->auth->model->id)
                ->addCondition('booking_date',$data['date'])
                ->tryLoadany();

        if($old->loaded()){
            echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"you have already made a booking for same date, please wait for confirmation"
                        ));
            exit;
        }

        



    }

}