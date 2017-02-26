<?php

// used to save cart detail into wish list
class endpoint_v1_post_cart extends HungryREST {
    public $model_class = 'Wishlist';
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

        // try{
            $return_array = $m->addToWish(
                                    $this->api->auth->model->id,
                                    $data['event_ticket_id'],
                                    $data['qty'],
                                    $data['unit_price'],
                                    $data['discount_voucher'],
                                    $data['discount_amount']
                                );
        // }catch(\Exception $e){
        //     return json_encode(array(
        //             'status'=>"failed",
        //             'message'=>'server error '
        //         ));
        // }
        return json_encode($return_array);
	}

	function delete($data){
        return "you are not allow to access";   
	}

    function validateParam($data){
        $required_param = [
                    'event_ticket_id'=>'int',
                    'qty'=>'int',
                    'unit_price'=>'number',
                    'discount_voucher'=>'varchar',
                    'discount_amount'=>'int'
                ];
        foreach ($required_param as $param =>$type) {
            if(!array_key_exists($param, $data)){
                echo "Param Error 1001 ";
                exit;
            }
        }
    }

}