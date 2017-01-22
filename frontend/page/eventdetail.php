<?php

class page_eventdetail extends Page{
	
    public $gallery_model;
	public $event_model;
	public $event_id=0;

    function init(){
        parent::init();
        
    	//loading required model
        $slug = trim($this->api->stickyGET('slug'));
    	$this->event_model = $event_model = $this->add('Model_Event')
                            ->addCondition('url_slug',$slug)
                            ->addCondition('is_active',true)
                            ->addCondition('is_verified',true)
                            ;
        $event_model->tryLoadAny();

        if(!$event_model->loaded()){
            $this->app->redirect($this->app->url('404'));
            exit;
        }

        $this->event_id = $id = $event_model->id;
                      
    	$this->gallery_model = $this->add('Model_EventImage')->addCondition('event_id',$id);
        $this->setModel($event_model);

        if($event_model['remaining_tickets']){
            $bookticket_btn  = $this->add('Button','null','bookticket')->set('Book Ticket')->addClass('atk-swatch-orange btn-block')->setStyle('border','0px solid white');
            $bookticket_btn->js('click')->univ()->location($this->api->url('bookticket',['slug'=>$event_model['url_slug']]));
        }

        //Add Route Map
        $view_route_map = $this->add('View_RouteMap',['restaurant_lat'=>$event_model['latitude'],'restaurant_lng'=>$event_model['longitude']],'routemap');
        $this->add('View_RouteMap',['restaurant_lat'=>$event_model['latitude'],'restaurant_lng'=>$event_model['longitude'],'zoom'=>3],'large_route_map');
        //set meta tags
        $this->setTitle($event_model['title']);
        $this->setMetaTag('title',$event_model['title']);
        $this->setMetaTag('keyword',$event_model['keyword']);
        $this->setMetaTag('description',$event_model['description']);
    }

    function setModel($m){

        parent::setModel($m);
        $banner_image_url =  str_replace("public/", "", $this->model['banner_image']);
        $logo_image_url = str_replace("public/", "", $this->model['logo_image']);
        
        $this->template->set('event_banner_image',$banner_image_url);
        $this->template->set('event_logo_image',$logo_image_url);

        // Date Format
        $this->template->trySet('starting_date_redable',date('M-d-Y',strtotime($this->model['starting_date'])));
        $this->template->trySet('starting_time_redable',date('H:i a',strtotime($this->model['starting_time'])));
        $this->template->trySet('closing_date_redable',date('M-d-Y',strtotime($this->model['closing_date'])));
        $this->template->trySet('closing_time_redable',date('H:i a',strtotime($this->model['closing_time'])));
        
        $this->template->trySetHtml('event_attraction_list',$this->model['event_attraction']);
        $this->template->trySetHtml('detail_html',$this->model['detail']);
        $this->template->trySetHtml('guidelines_html',$this->model['guidelines']);
        $this->template->trySetHtml('how_to_reach_html',$this->model['how_to_reach']);
        $this->template->trySetHtml('disclaimer_html',$this->model['disclaimer']);
        // $event = $this->add('View_EventTicket');
        // $event->setModel($m);
    }

    function recursiveRender(){
        $this->add('View_RedefineSearch',null,'redefine_search');
        
        $gallery = $this->add('View_Lister_EventGallery',['event_id'=>$this->event_id],'gallery');
    	$gallery->setModel($this->gallery_model);

        //ticket price
        $days = $this->add('Model_Event_Day')
                    ->addCondition('event_id',$this->event_id)
                    ->setOrder('on_date','asc');

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

        $vouchers = $this->add('Model_Voucher')->addCondition('event_id',$this->event_id);
        $v_list = $this->add('View_Lister_EventVoucher',null,'discount_vouchers');
        $v_list->setModel($vouchers);
        //upcomming event
        $upcoming_event = $this->add('Model_Event');
        $upcoming_event->addCondition('starting_date','>=',$this->api->today);
        $upcoming_event->addCondition('id',"<>",$this->event_model->id);
        $upcoming_event->addCondition('is_active',true);
        $upcoming_event->addCondition('is_verified',true);
        $upcoming_event->setOrder('starting_date','asc');
        $upcoming_event->setLimit(3);

        if($upcoming_event->count()->getOne())
            $this->add('View_Lister_Event',['template'=>'view/upcomingevent','header'=>'Upcoming Events'],'upcoming_event')->setModel($upcoming_event);
        else
            $this->template->tryDel('upcoming_wrapper');

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

        // downloadapp
        $this->add('View_DownloadApp',null,'downloadapp');
    	parent::recursiveRender();
    }

    function defaultTemplate(){
    	return ['page/eventdetail'];
    }
}