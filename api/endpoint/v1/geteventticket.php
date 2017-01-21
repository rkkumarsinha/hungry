<?php

class endpoint_v1_geteventticket extends HungryREST {
    public $model_class = 'UserEventTicket';
    public $allow_list=true;
    public $allow_list_one=false;
    public $allow_add=false;
    public $allow_edit=false;
    public $allow_delete=false;
    public $totalRecord = 0;

    function init(){
        parent::init();

        //check authorization here for second time
    }

    function authenticate(){
        $data = parent::authenticate();
        if($data['status'] === "success")
            return true;

        echo json_encode($data);
        exit;
    }

    function get(){

        $m = $this->model;
        
        if(!$m)throw $this->exception('Specify model_class or define your method handlers');

        if ($m->loaded()) {
            if(!$this->allow_list_one)throw $this->exception('Loading is not allowed');
            return "list one not allowed";
        }
        
        if(!$this->allow_list)throw $this->app->exception('Listing is not allowed');
        
        // $output = $this->outputMany($m);
        $output = [];
        $count = 1;
        $first_id = 0;
        $last_id = 0;

        foreach ($m as $model) {
            if($count===1)
                $first_id = $model->id;
            
            $ticket = $this->add('Model_Event_Ticket')->tryLoad($model['event_ticket_id']);
            if(!$ticket->loaded())
                continue;
            
            $event = $this->add('Model_Event')->tryLoad($ticket['event_id']);
            if(!$event->loaded())
                continue;

            $output[$model->id] = [
                        "event_name"=>$event['name'],
                        "event_id"=>$event['id'],
                        "event_address"=>$event['address'],
                        "event_image"=>$event['display_image'],
                        "event_day"=>$ticket['event_day'],
                        "event_time"=>date("H:i:s", strtotime($ticket['event_time'])),

                        "ticket_booking_no"=>$model['ticket_booking_no'],
                        "ticket_id"=>$ticket['id'],
                        "ticket_name"=>$ticket['name'],
                        "detail"=>$ticket['detail'],

                        'qty'=>$model['qty'],
                        "price"=>$model['price'],
                        "offer_percentage"=>$model['offer_percentage'],
                        'total_amount'=>$model['total_amount'],
                        'offer_amount'=>$model['offer_amount'],
                        'net_amount'=>$model['net_amount'],
                        "amount_paid"=>$model['amount_paid'],
                        "booking_name"=>$model['booking_name'],
                        "status"=>$model['status'],
                        "payment_mode"=>$model['payment_mode'],
                        'created_at'=>$model['created_at']
                    ];
            $last_id = $model->id;
            $count++;
        }
            
        // var_dump($first_id);
        // var_dump($last_id);
        // exit;
        $data = ['list'=>array_values($output)];

        $next_url = null;
        $previous_url = null;
        
        $next_offset = $_GET['offset'] + $_GET['limit'];
        $previous_offset = $_GET['offset'] - $_GET['limit'];
        if($previous_offset < 0){
            $previous_offset = 0;
        }

        if($_GET['type'] === "next"){
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }elseif($_GET['type'] === "previous"){
            $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if($this->totalRecord > 1)
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }else{
            if($this->totalRecord > $_GET['limit'])
                $next_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$next_offset,'type'=>"next",'for'=>$_GET['for']]);
            if(isset($_GET['offset']) and ($_GET['offset'] > 0) )
                $previous_url = $this->app->getConfig('apipath').$this->app->url(null,['limit'=>$_GET['limit'],'offset'=>$previous_offset,'type'=>"previous",'for'=>$_GET['for']]);
        }
        // echo "<pre>";
        // print_r($output);
        // exit;
        $data['previous_url'] = $previous_url;
        $data['next_url'] = $next_url;
        
        return $data;
    }

    function _model(){
        $this->validateParams();
        
        $model = parent::_model();

        switch ($_GET['for']) {
            case 'user':
                $model->addCondition('user_id',$this->api->auth->model->id);
                break;
            case 'event':
                $model->addCondition('event_ticket_id',$_GET['eventid']);
                break;
        }

        // if($_GET['type'] === "next"){
        //     $model->addCondition('id','>',$_GET['offset']);
        // }elseif($_GET['type'] === "previous"){
        //     $model->addCondition('id','<',$_GET['offset']);
        //     $model->setOrder('id','desc');
        // }else{
        //     $offset = 0;
        // }

        $offset = 0;
        if($_GET['offset'] > 0)
            $offset = $_GET['offset'];

        $this->totalRecord = $model->count()->getOne();
        if($_GET['limit']){
            $model->setLimit($_GET['limit'],$offset);
        }

        if($this->totalRecord == 0){
            echo "no record found";
            exit;
        }
        return $model;
    }

	function put($data){
        // return json_encode($data);
        return "you are not allow to access";
	}

	function delete($data){
        return "you are not allow to access";   
	}

    private function validateParams(){

        if(!$_GET['limit'])
            throw new \Exception("some thing wrong ...1001"); //must pass limit

        if($_GET['type'] and !in_array($_GET['type'], array('next','previous')))
            throw new \Exception("some thing wrong...1002", 1); //type must be in array

        if(!$_GET['for'] or !in_array($_GET['for'],['user','event']))
            throw new \Exception("some thing wrong...1003", 1);
    }
}