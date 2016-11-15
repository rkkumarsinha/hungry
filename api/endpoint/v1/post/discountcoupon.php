<?php

class endpoint_v1_post_discountcoupon extends HungryREST {
    public $model_class = 'DiscountCoupon';
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
        
        if(!$m->checkDiscountQuota($data['restaurant_id'],$this->api->auth->model,$data['discount_id'],$data['offer_id']))
            return json_encode(array('status'=>"Failed",'message'=>'today limit exceeded, or you already have discount coupon of the same restaurant, please try after 24 Hours '));
        
        try{
            $m->set($data);
            $m['user_id'] = $this->api->auth->model->id;
            $m['is_send'] = 1;
            $m->save();
            $m->sendDiscount();
        }catch(\Exception_StopInit $e){

        }catch(Exception $e){
            if($m->loaded()){
                $m['is_send'] = 0;
                $m->save();
            }
            $msg = $e->getMessage();
            if(!$msg)
                $msg = "if you not receive booking email or sms. please contact to respective restaurant or hungrydunia";

            return json_encode(array(
                            'status'=>"failed",
                            'message'=>$msg
                        ));            
        }

        return json_encode(array(
                            'status'=>"success",
                            'message'=>"congratulation! your discount coupon has been successfully send.",
                            'discount_coupon'=>$m['discount_coupon']
                        ));
    }

    function delete($data){
        return "you are not allow to access";
    }

    function validateParam($data){
        $required_param = ['name','email','mobile','discount_id','offer_id','restaurant_id'];
                
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo json_encode(array(
                            'status'=>"failed",
                            'message'=>"Param Error 1001"
                        ));
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
                            'message'=>"either Discount or Offer"
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

            $appli_offer = $this->add('Model_RestaurantOffer')
                            ->addCondition('restaurant_id',$rest->id)
                            ->addCondition('id',$om->id);
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
        

    }
}