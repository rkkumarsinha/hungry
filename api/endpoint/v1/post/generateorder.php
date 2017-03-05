<?php

class endpoint_v1_post_generateorder extends HungryREST {
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

        $return_array = [];

        $wishlist = $this->add('Model_Wishlist')
                    ->addCondition("user_id",$this->app->auth->model->id)
                    ->addCondition("is_wishcomplete",false)
                    ;
        if(!$wishlist->count()->getOne()) return ['status'=>'failed','message'=>'your cart is empty'];
        
        try{
            $order = $m->placeOrderFromWishList($data);
            if($order instanceof Model_Invoice){
                $order->reload();

                $return_array = [
                            'order_id'=>$order->id,
                            'amount'=>$order['net_amount'],
                            'billing_name'=>$order['billing_name'],
                            'billing_address'=>$order['billing_address'],
                            'billing_city'=>$order['billing_city'],
                            'billing_state'=>$order['billing_state'],
                            'billing_zip'=>$order['billing_zip'],
                            'billing_country'=>$order['billing_country'],
                            'billing_tel'=>$order['billing_tel'],
                            'billing_email'=>$order['billing_email'],
                            'billing_email'=>$order['billing_email']
                        ];
            }else{
                
                $return_array = $order;
            }

        }catch(\Exception $e){
            return $return_array = array(
                    'status'=>"failed",
                    'message'=>'error at time of generation order'
                );
        }

        return json_encode($return_array);
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = [
                    'billing_name',
                    'billing_address',
                    'billing_city',
                    'billing_state',
                    'billing_zip',
                    'billing_country',
                    'billing_tel',
                    'billing_email'
                ];
        foreach ($required_param as $param) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001 ";
                exit;
            }
            if(!$data[$param]){
                echo "Param Error 1002 ";
                exit;
            }
        }
    }
}