<?php

class page_account extends Page{

    function init(){
        parent::init();


        if(!$this->app->auth->model->id){
            $this->app->memorize('next_url','account');
            $this->app->redirect($this->app->url('signin'));
        }

        
        if($this->app->auth->model['type'] === "host"){
            $alllist =  $this->api->auth->model->getAllListing();
            $count = 1;
            foreach ($alllist as $key => $value) {
                if($count > 1)
                    break;
                $data = explode("-", $key);
            }

            if(!$this->app->recall('HOSTLISTID')){
                $this->app->memorize('HOSTLISTID',$data[0]);
                $this->app->memorize('HOSTLISTTYPE',$data[1]);
            }

            $list_id = $this->app->recall('HOSTLISTID');
            $list_type = $this->app->recall('HOSTLISTTYPE');
            $this->app->listmodel = $this->add('Model_'.$list_type)->load($list_id);
            
            $this->template->trySet('list_data',$this->app->listmodel['name']);
            $this->add('View_HostAccount_'.$this->app->recall('HOSTLISTTYPE'));
        
        }else{
            $tabs = $this->add('Tabs',null,null,['view/hungrytabs']);
            $profile_tab =  $tabs->addTab('Profile');
            $booking_tab = $tabs->addTab('My Booking');
            $coupon_tab = $tabs->addTab('Coupons');
            $history_tab = $tabs->addTab('Event Ticket');
            $review_tab = $tabs->addTab('My Reviews');

            $profile_tab->add('View_Account_Profile');
            $booking_tab->add('View_Account_MyBooking');
            $coupon_tab->add('View_Account_Coupon');
            $history_tab->add('View_Account_EventTicket');
            $review_tab->add('View_Account_Review');
        }
    }

    function defaultTemplate(){
        return ['page/account'];
    }
}