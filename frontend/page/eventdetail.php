<?php

class page_eventdetail extends Page{
	
    public $gallery_model;
	public $event_model;
	public $event_id=0;

    function init(){
        parent::init();
        
    	//loading required model
        $slug = trim($this->api->stickyGET('slug'));
    	$this->event_model = $event_model = $this->add('Model_Event')->addCondition('url_slug',$slug);
        $event_model->tryLoadAny();

        if(!$event_model->loaded()){
            throw new \Exception("Page Not Found");
            exit;
        }

        $this->event_id = $id = $event_model->id;
                      
    	$this->gallery_model = $this->add('Model_EventImage')->addCondition('event_id',$id);
        $this->setModel($event_model);

        $bookticket_btn  = $this->add('Button','null','bookticket')->set('Book Ticket')->addClass('atk-swatch-orange btn-block')->setStyle('border','0px solid white');
        
        $bookticket_btn->js('click')->univ()->location($this->api->url('bookticket'));
        // if($getdiscount_btn->isClicked()){
            // 'autoOpen'=>false,'show'=>array('effect'=>'blind','duration'=> 1000)
            // $options = array('width'=>'800');
            // $this->js()->univ()->frameURL('Book Your Ticket',$this->api->url('bookticket',array('event_id'=>$id,'cut_page'=>0)),$options)->execute();
        // }

        //Add Route Map
        $view_route_map = $this->add('View_RouteMap',['restaurant_lat'=>$event_model['latitude'],'restaurant_lng'=>$event_model['longitude']],'routemap');
    }

    function setModel($m){

        parent::setModel($m);
        $banner_image_url =  str_replace("public/", "", $this->model['banner_image']);
        $logo_image_url = str_replace("public/", "", $this->model['logo_image']);
        
        $this->template->set('event_banner_image',$banner_image_url);
        $this->template->set('event_logo_image',$logo_image_url);
        // $event = $this->add('View_EventTicket');
        // $event->setModel($m);
    }

    function recursiveRender(){
        
        $gallery = $this->add('View_Lister_EventGallery',['event_id'=>$this->event_id],'gallery');
    	$gallery->setModel($this->gallery_model);

        //ticket price
        $days = $this->add('Model_Event_Day')->addCondition('event_id',$this->event_id);
        $all_str = '';
        $event_day_time = "";
        $ticket_and_detail = "";
        foreach ($days as $day) {

            $temp = '<div class="event-ticket-day"><h4>'.$day['name'].'</h4>';

            $event_day_time .=  $temp;
            $all_str .= $temp;
            $ticket_and_detail .= $temp;

            $times = $this->add('Model_Event_Time')->addCondition('event_day_id',$day->id);
            foreach ($times as $time) {

                $temp1 = '<div class="event-ticket-time"><h5>'.$time['name'].'</h5>';
                $all_str .= $temp1;
                $event_day_time .= $temp1;
                $ticket_and_detail .= $temp1;

                $tickets = $this->add('Model_Event_Ticket')->addCondition('event_time_id',$time->id);
                foreach ($tickets as $ticket) {
                    $temp2 = "<p>".$ticket['name'].':<span class="pull-right">'.$ticket['price']."</span></p>";
                    $all_str .= $temp2;
                    $ticket_and_detail .= '<p style="margin:0px;font-weight:bold;">'.$ticket['name'].':<span class="pull-right">price='.$ticket['price']."</span></p>";
                    if($ticket['detail'] != "")
                        $ticket_and_detail .='<div style="padding-left:10px;margin-bottom:15px;">'.$ticket['detail']."</div>";
                }

                $all_str .= "</div>";
                $ticket_and_detail .= "</div>";

            }

            $all_str .= "</div>";
            $ticket_and_detail .= "</div>";
            $event_day_time .= "</div>";
        }

        //ticket price
        $this->add('View',null,'ticket_price')->setHtml($all_str);
        //event ticket day time        
        // $this->add('View',null,'event_day_time')->setHtml('<div class="hungry-event-timing">'.$event_day_time."</div>");
        //ticket and their detail
        $this->add('view',null,'ticket_and_their_detail')->setHtml('<div class="hungry-event-ticket-and-detail">'.$ticket_and_detail."</div>");
        // $event = $this->add('View_EventTicket',null,'ticket_and_their_detail');
        // $event->setModel($this->model);

        //upcomming event
        $upcoming_event = $this->add('Model_Event');
        $upcoming_event->addCondition('starting_date','>=',$this->api->today);
        $upcoming_event->addCondition('id',"<>",$this->event_model->id);
        $upcoming_event->setOrder('starting_date','asc');
        $upcoming_event->setLimit(3);

        $this->add('View_Lister_Event',['template'=>'view/upcomingevent','header'=>'Upcoming Events'],'upcoming_event')->setModel($upcoming_event);

        //checking all if value has or not
        if(!$this->model['mobile_no'])
            $this->template->tryDel('mobile_wrapper');

        if(!$this->model['phone_no'])
            $this->template->tryDel('phone_wrapper');

        if(!$this->model['email'])
            $this->template->tryDel('email_wrapper');
        
        if(!$this->model['website'])
            $this->template->tryDel('website_wrapper');
        
        if(!$this->model['facebook_page_url'])
            $this->template->tryDel('facebook_wrapper');
        
        if(!$this->model['instagram_page_url'])
            $this->template->tryDel('instagram_wrapper');

    	parent::recursiveRender();
    }

    function defaultTemplate(){
    	return ['page/eventdetail'];
    }
}