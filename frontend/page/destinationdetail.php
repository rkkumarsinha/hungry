<?php

class page_destinationdetail extends Page{
	
    public $gallery_model;
	public $destination_model;
	public $event_id=0;

    function init(){
        parent::init();

        //loading required model
        $slug = trim($this->api->stickyGET('slug'));
        $destination_model = $this->add('Model_Destination')
                ->addCondition('url_slug',$slug)
                ->addCondition('status','active')
                ->addCondition('is_verified',true)
                ;
        $destination_model->tryLoadAny();

        if(!$destination_model->loaded()){
            $this->app->redirect($this->app->url('404'));
            exit;
        }

        $this->destination_id = $id = $destination_model->id;
                      
        $this->gallery_model = $this->add('Model_DestinationImage')->addCondition('destination_id',$id);

        $this->setModel($destination_model);

        //Add Route Map
        $view_route_map = $this->add('View_RouteMap',['restaurant_lat'=>$destination_model['latitude'],'restaurant_lng'=>$destination_model['longitude']],'routemap');
    }

    function setModel($m){

        parent::setModel($m);

        $banner_image_url =  str_replace("public/", "", $this->model['banner_image']);
        $this->template->set('destination_banner_image',$banner_image_url);
    }

    function recursiveRender(){
        
        $gallery = $this->add('View_Lister_DestinationGallery',['destination_id'=>$this->destination_id],'gallery');
        $gallery->setModel($this->gallery_model);

        //Add Facilitiy
        $facility_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->destination_id)
                        ->addCondition('highlight_type',"facility")
                        ->addCondition('is_active',true)
                        ;
        $this->add('Lister',null,'facilites',['page/destinationdetail','facilites'])->setModel($facility_model);

        //add Occassion/ Destination For
        $occasion_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->destination_id)
                        ->addCondition('highlight_type',"occasion")
                        ->addCondition('is_active',true)
                        ;
        $this->add('Lister',null,'occassion',['page/destinationdetail','occassion'])->setModel($occasion_model);

        //service available
        $service_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->destination_id)
                        ->addCondition('highlight_type',"service")
                        ->addCondition('is_active',true)
                        ;
        $this->add('Lister',null,'service',['page/destinationdetail','service'])->setModel($service_model);

        // Venue association
        $venue_model = $this->add('Model_Destination_VenueAssociation')
                        ->addCondition('destination_id',$this->destination_id)
                        ;
        $this->add('Lister',null,'venue',['page/destinationdetail','venue'])->setModel($venue_model);
        
        //space association
        $space_model = $this->add('Model_Destination_Space')
                        ->addCondition('is_active',true)
                        ->addCondition('destination_id',$this->destination_id)
                        ;
        $this->add('Lister',null,'space',['page/destinationdetail','dest_space'])->setModel($space_model);
        $this->add('Lister',null,'dest_space',['page/destinationdetail','dest_space'])->setModel($space_model);

        // //Destination Packages
        $package_model = $this->add('Model_Destination_Package')
                        ->addCondition('is_active',true)
                        ->addCondition('destination_id',$this->destination_id)
                        ;
        $this->add('Lister',null,'packages',['page/destinationdetail','packages'])->setModel($package_model);
        

        //Similar Destination
        $similar_destination_model = $this->add('Model_Destination')->setLimit(3);
        $list = $this->add('View_Lister_Destination',['template'=>'view/similardestination'],'similar_destination');
        $list->setModel($similar_destination_model);


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

        // Booking Request form
        $enquiry_form = $this->add('View_RequestToBook',null,'enquiryform');
        $js_event = [
                        $this->js()->_selector('#destination_enquiry_modalpopup')->modal('show'),
                        $enquiry_form->js()->reload(['destination_id'=>$this->js()->_selectorThis()->attr('data-destinationid')]),
                    ];
        $this->js('click',$js_event)->_selector('.hungrydestination_enquiry');

        parent::recursiveRender();
    }

    function defaultTemplate(){
        return ['page/destinationdetail'];
    }
}