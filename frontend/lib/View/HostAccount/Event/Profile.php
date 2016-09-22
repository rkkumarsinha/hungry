<?php

class View_HostAccount_Event_Profile extends View{
	function init(){
		parent::init();
	
		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");
		
		$event_model = $this->app->listmodel;

		$tab = $this->add('Tabs');
		$basic_info_tab = $tab->addTab('Basic Info');
		$image_gallery_tab = $tab->addTab('Image Gallery');
		$eventday_tab = $tab->addTab('Event Day');
		$ticket_tab = $tab->addTab('Ticket');
		
		$basic_form = $basic_info_tab->add('Form');
		$basic_form->setModel($event_model,
							[
								'country',
								'country_id',
								'state',
								'state_id',
								'city_id',
								'city',
								'area_id',
								'area',
								'logo_image_id',
								'logo_image',
								'banner_image_id',
								'banner_image',
								'display_image_id',
								'display_image',
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
								'guidelines',
								'how_to_reach',
								'disclaimer'
							]);

		$basic_form->addSubmit("Save");
		if($basic_form->isSubmitted()){
			$basic_form->save();
			
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for Event Approved";
			$notification['from_id'] = $event_model->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "Event";
			$notification['status'] = "pending";
			$notification['value'] = $event_model['id'];
			$notification->save();

			$basic_form->js()->univ()->successMessage("Saved Successfully")->execute();
		}

		$crud = $image_gallery_tab->add('CRUD');
        $event_image = $this->add('Model_EventImage')
        				->addCondition('event_id',$event_model->id)
        				;

        $event_image->addHook('beforeSave',function($m){
        	$m['status'] = "pending";
        });
        $event_image->addHook('afterInsert',function($model)use($event_model){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new Event Gallery Image Approved";
			$notification['from_id'] = $event_model->id;
			$notification['from'] = "Event";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "image";
			$notification['status'] = "pending";
			$notification['value'] = $model['id'];
			$notification->save();
        });

        $crud->setModel($event_image,['image_id','image','is_active'],['image','status','is_active']);
        $crud->grid->addPaginator(10);
        $crud->grid->addHook('formatRow',function($g){
            if($g->model['image_id']){
                $f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
                $f->tryLoadAny();
                if($f->loaded()){
                    $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                    $g->current_row_html['image'] = "<img width='100px' src=".$path.">";
                }else
                    $g->current_row_html['image'] = "No Icon Found";
            }else
                $g->current_row_html['image'] = "No Icon Found";
        });


        $event_day_model = $eventday_tab->add('Model_Event_Day')->addCondition('event_id',$event_model->id);
        $day_crud = $eventday_tab->add('CRUD');
        $day_crud->setModel($event_day_model);
        $day_crud->grid->add('VirtualPage')
			->addColumn('event_time')
			->set(function($page)use($event_model){
				$event_day_id = $_GET[$page->short_name.'_id'];

				$time_crud = $page->add('CRUD');
				$time_model = $page->add('Model_Event_Time')
								->addCondition('event_day_id',$event_day_id)
								->addCondition('event_id',$event_model->id)
								;
				$time_crud->setModel($time_model,['name'],['event_day','name','on_date']);
			});
		$day_crud->grid->addPaginator(10);

		// Ticket
		$ticket_crud = $ticket_tab->add('CRUD');
		$event_time_model = $ticket_tab->add('Model_Event_Time')
							->addCondition('event_id',$event_model->id)
							;
		$ticket_crud->setModel($event_time_model,['name'],['event_day','name','on_date']);
		$ticket_crud->grid->add('VirtualPage')
			->addColumn('event_ticket')
			->set(function($page)use($event_model){
				$event_time_id = $_GET[$page->short_name.'_id'];

				$ticket_crud = $page->add('CRUD');
				$ticket_model = $page->add('Model_Event_Ticket')
								->addCondition('event_time_id',$event_time_id)
								->addCondition('event_id',$event_model->id)
								;
				$ticket_crud->setModel($ticket_model,
										['event_id','event_time_id','name','price','detail','offer','applicable_offer_qty','offer_percentage','max_no_to_sale','disclaimer'],
										['name','price','offer_percentage']
									);
			});
		$ticket_crud->grid->addPaginator(10);

	}
}