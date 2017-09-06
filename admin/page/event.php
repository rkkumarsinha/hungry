<?php

class page_event extends page_adminevent{
	 public $title='Event';

	function init(){
		parent::init();
		
		$crud = $this->add('CRUD');

        $event_model = $this->add('Model_Event');
        $event_model->addExpression('user_status')->set(function($m,$q){
            return $m->refSQL('user_id')->fieldQuery('is_verified');
        });
        $event_model->setOrder('id','desc');
		$crud->setModel($event_model,
						array(
                                'user_id',
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
                                'how_to_reach',
                                'is_active',
                                'is_verified',
                                'disclaimer',
                                'tax_percentage',
                                'handling_charge',
                                'is_free_ticket',
                                'registration_url',
                                'is_active'
                            ),
                        array
                            (    
                                'user',
                                'name',
                                'user_status',
                                'starting_date',
                                'closing_date',
                                'total_day',
                                'event_category',
                                'lowest_price'
                            )
                        );
        $crud->grid->addPaginator($ipp=30);
        $crud->grid->addQuickSearch(['name']);

        $crud->grid->add('VirtualPage')
            ->addColumn('Days')
            ->set(function($page){
            	$event_id = $_GET[$page->short_name.'_id'];

            	$day = $page->add('Model_Event_Day')->addCondition('event_id',$event_id);
            	$day_crud = $page->add('CRUD');
            	$day_crud->setModel($day);
	            	// $day_crud->addColumn
            });
		$crud->grid->addHook('formatRow',function($g){
            $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('verify_event',['id'=>$g->model['id'],'type'=>'event']).'">'.$g->model['name'].'</a>';
            // $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('restaurantdetail',['rest_id'=>$g->model['id']]).'">'.$g->model['name'].'</a>';
            $g->current_row_html['user_status'] = $g->model['user_status']?'<div class="atk-swatch-green" style="padding:2px;text-align:center;">verified</div>':'<div class="atk-swatch-red" style="padding:2px;text-align:center;">to be verified</div>';
        });

        $crud->grid->add('VirtualPage')
            ->addColumn('send_email_verification')
            ->set(function($page){
                $id = $_GET[$page->short_name.'_id'];

                $business_model = $event = $this->add('Model_Event')->load($id);

                if(!$event['user_id']){
                    $page->add('View_Error')->set('Host not found');
                    return;
                }

                $user = $page->add('Model_User')->tryLoad($event['user_id']);
                if(!$user->loaded()){
                    $page->add('View_Error')->set('Host not found');
                    return;
                }

                if($user['type'] != "host"){
                    $page->add('View_Error')->set('Host not found, user is not host type '.$user->id);
                    return;
                }
                
                if($user['is_verified']){
                    $page->add('View_Info')->set('Host is verified, do you want to send email again'.$user['is_verified']);
                }

                $email_template = $page->add('Model_EmailTemplate')
                                ->addCondition('name',"EMAILVERIFICATIONHOST")
                                ->tryLoadAny();
                $subject = $email_template['subject'];
                $body = $email_template['body'];

                $body = str_replace("{user_name}", $user['name'], $body);
                $body = str_replace("{business_name}", $business_model['name'], $body);
                $body = str_replace("{verification_email_link}", $user->getVerificationURL()."&business=".$business_model->id."&business_type=event", $body);

                $form = $page->add('Form');
                $form->add('View')->setHtml($body);
                $form->addSubmit('Send Verification');

                if($form->isSubmitted()){
                    $outbox = $this->add('Model_Outbox');
                    try{
                        $email_response = $outbox->sendEmail($user['email'],$subject,$body,$user);
                        $outbox->createNew("Verification Email From Admin",$user['email'],$subject,$body,"Email","New Host User Registration with ".$business_name['name'],$user->id,$user_model);
                    }catch(Exception $e){
                        // $form->js()->univ()->errorMessage('email not send')->execute();
                    }
                    $form->js()->univ()->successMessage('email send successfully')->execute();
                }             
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
            	$ticket = $page->add('Model_Event_Ticket',["time_title_field"=>'event_time_day'])->addCondition('event_id',$event_id);
            	$ticket_crud = $page->add('CRUD');
                
                // if($ticket_crud->isEditing()){
                //     $event_day_field = $ticket_crud->form->addField('DropDown','event_day');
                //     $event_day_field->setModel($page->add('Model_Event_Day')->addCondition('event_id',$event_id)->setOrder('id','asc'));
                // }

                $ticket_crud->setModel($ticket,array('event_time_id','name','price','detail','applicable_offer_qty','offer','offer_percentage','max_no_to_sale','disclaimer','is_voucher_applicable'),array('event_time','name','price','detail','applicable_offer_qty','offers','offer_percentage','max_no_to_sale','disclaimer','is_voucher_applicable'));

                if($ticket_crud->isEditing()){
                    $event_time_model = $ticket_crud->form->getElement('event_time_id')
                                        ->getModel();
                    $event_time_model->addCondition('event_id',$event_id);
            	}
            });

        $crud->grid->add('VirtualPage')
            ->addColumn('Gallery')
            ->set(function($page){
            	$event_id = $_GET[$page->short_name.'_id'];
            	$images = $page->add('Model_EventImage')->addCondition('event_id',$event_id);
            	$images_crud = $page->add('CRUD');
            	$images_crud->setModel($images,array('event_id','name','is_active','image_id','status'),array('event','name','is_active','image','status'));

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