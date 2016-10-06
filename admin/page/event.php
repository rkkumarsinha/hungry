<?php

class page_event extends Page{
	 public $title='Event';

	function init(){
		parent::init();
		
		$crud = $this->add('CRUD');
		$crud->setModel('Model_Event',
						array(
								'country_id',
								'state_id',
								'city_id',
								'area_id',
								'restaurant_id',
								'event_category_id',
								'logo_image_id',
                                'banner_image_id',
                                'display_image_id',
								'name',
								'owner_name',
								'detail',
								'address',
								'mobile_no',
								'phone_no',
								'email',
								'website',
								'facebook_page_url',
								'instagram_page_url',
								'starting_date',
                                'starting_time',
								'closing_date',
                                'closing_time',
								'longitude',
								'latitude',
								'url_slug',
								'lowest_price',
								'event_attraction',
                                'guidelines',
                                'how_to_reach'
							),
						array
							(
								'name',
								'starting_date',
								'closing_date',
								'total_day',
								'event_category',
								'lowest_price'
							)
						);

		$crud->grid->add('VirtualPage')
            ->addColumn('Days')
            ->set(function($page){
            	$event_id = $_GET[$page->short_name.'_id'];
            	$day = $page->add('Model_Event_Day')->addCondition('event_id',$event_id);
            	$day_crud = $page->add('CRUD');
            	$day_crud->setModel($day,array('name','on_date'));
	            	// $day_crud->addColumn
            });
		
		$crud->grid->add('VirtualPage')
            ->addColumn('Times')
            ->set(function($page){            	
            	$event_id = $_GET[$page->short_name.'_id'];
            	$time = $page->add('Model_Event_Time')
            			->addCondition('event_id',$event_id);
            	$time_crud = $page->add('CRUD');
            	$time_crud->setModel($time,array('event_day_id','name'),array('event_day','name'));

            	if($time_crud->isEditing()){
            		$time_crud->form->getElement('event_day_id')->getModel()->addCondition('event_id',$event_id);
            	}
            	
            });
		
		$crud->grid->add('VirtualPage')
            ->addColumn('Ticket')
            ->set(function($page){
            	$event_id = $_GET[$page->short_name.'_id'];
            	$ticket = $page->add('Model_Event_Ticket')->addCondition('event_id',$event_id);
            	$ticket_crud = $page->add('CRUD');
            	$ticket_crud->setModel($ticket,array('event_time_id','name','price','detail','applicable_offer_qty','offer','offer_percentage','max_no_to_sale','disclaimer'),array('event_time','name','price','detail','applicable_offer_qty','offers','offer_percentage','max_no_to_sale','disclaimer'));

	            if($ticket_crud->isEditing()){
            		$ticket_crud->form->getElement('event_time_id')->getModel()->addCondition('event_id',$event_id);
            	}
            });

        $crud->grid->add('VirtualPage')
            ->addColumn('Gallery')
            ->set(function($page){
            	$event_id = $_GET[$page->short_name.'_id'];
            	$images = $page->add('Model_EventImage')->addCondition('event_id',$event_id);
            	$images_crud = $page->add('CRUD');
            	$images_crud->setModel($images,array('event_id','name','is_active','image_id'),array('event','name','is_active','image'));

                $images_crud->grid->addHook('formatRow',function($g){
                    if($g->model['image_id']){
                        $f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
                        $f->tryLoadAny();
                        if($f->loaded()){
                            $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                            $g->current_row_html['image'] = "<img width='100px;' src=".$path.">";
                        }else
                            $g->current_row_html['image'] = "No Icon Found";
                    }else
                        $g->current_row_html['image'] = "No Icon Found";
                });

            });

       	$crud->grid->add('VirtualPage')
       			->addColumn('Place')
       			->set(function($page){

            		$event_id = $_GET[$page->short_name.'_id'];
            		$tab = $page->add('Tabs');
            		$destination_tab = $tab->addTab('Destination');
            		$rest_tab = $tab->addTab('Restaurant');

            		$dest_model = $destination_tab->add('Model_EventDestinationRest')
    					->addCondition('event_id',$event_id)
    					->addCondition('type',"destination");
    					;
	            	$dest_crud = $destination_tab->add('CRUD');
    	        	$dest_crud->setModel($dest_model,['destination_id'],['destination']);

    	        	//restaurant
    	        	$rest_model = $rest_tab->add('Model_EventDestinationRest')
    					->addCondition('event_id',$event_id)
    					->addCondition('type',"restaurant");
    					;
	            	$rest_crud = $rest_tab->add('CRUD');
    	        	$rest_crud->setModel($rest_model,['restaurant_id'],['restaurant']);

       			});

	}
}