<?php
class page_bookticket extends Page{

    function init(){
        parent::init();

        $event_id = $this->api->stickyGET('hungry_event_id');
        $this->api->stickyGET('slug');

        if($_GET['slug']){
            $event_model = $this->add('Model_Event')->addCondition('url_slug',$_GET['slug'])->tryLoadAny();
        }elseif($event_id){
            $event_model = $this->add('Model_Event')->tryLoad($event_id);
        }

        if(!$this->api->auth->model->id){
         $this->add('View_Login',['reload'=>"parent"]);
         return;
        }

        if(!$event_model->loaded()){
            $this->add('View_Error')->set('no record found');
            return;
        }

        //loading Event model
        $event_view = $this->add('View_EventTicket');
        $event_view->setModel($event_model);
        
    }
    function defaultTemplate(){
        return ['page/bookticket'];
    }
}

