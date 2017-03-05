<?php

// used to save cart detail into wish list
class endpoint_v1_post_confirmorder extends HungryREST {
    public $model_class = 'Invoice';
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

        try{
            $order = $this->add('Model_Invoice')->load($data['order_id']);
            $order->updateInvoiceTransaction($data);
            $order->reload();
            if($order['status'] == "Paid"){
                return ['status'=>"success",'message'=>'order payment paid successfully'];
            }else{
                return ['status'=>"failed",'message'=>'order payment failed'];
            }

        }catch(\Exception $e){
            return json_encode(array(
                    'status'=>"failed",
                    'message'=>'server error '
                ));
        }
        return json_encode($return_array);
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        if(count($data) > 9 ){
            echo "Param Error 1002";
            exit;
        }

        $required_param = [
                    'tracking_id',
                    'bank_ref_no',
                    'order_status',
                    'payment_mode',
                    'card_name',
                    'amount',
                    'trans_date',
                    'transaction_detail',
                    'order_id'
                ];
        foreach ($required_param as $param =>$type) {
            if(!array_key_exists($type, $data)){
                echo "Param Error 1001 ".$param;
                exit;
            }
        }

    }

}