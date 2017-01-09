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
         $this->add('View_Login',['reload'=>"parent"],'login_panel');
         return;
        }else{
            $this->template->tryDel('login_wrapper');
        }

        if(!$event_model->loaded()){
            $this->add('View_Error')->set('no record found');
            return;
        }

        //loading Event model
        $event_view = $this->add('View_EventTicket');
        $event_view->setModel($event_model);
        
        //seo meta tags
        $this->setTitle($event_model['title']);
        $this->setMetaTag('title',$event_model['title']);
        $this->setMetaTag('keyword',$event_model['keyword']);
        $this->setMetaTag('description',$event_model['description']);
    }
    function defaultTemplate(){
        return ['page/bookticket'];
    }
}

