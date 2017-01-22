<?php

class View_HostAccount_Destination_Profile extends View{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_destination = $this->app->listmodel;

		$tab = $this->add('Tabs');
		$basic_info_tab = $tab->addTab('Basic Info');
		$image_gallery_tab = $tab->addTab('Image Gallery');
		$highlight_tab = $tab->addTab('Highlight');
		$category_tab = $tab->addTab('Category');
		$space_tab = $tab->addTab('Space');
		$package_tab = $tab->addTab('Packages');
		
		$basic_form = $basic_info_tab->add('Form');
		$basic_form->setModel($host_destination,
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
								'about_destination',
								'address',
								'mobile_no',
								'phone_no',
								'email',
								'website',
								'facebook_page_url',
								'instagram_page_url',
								// 'rating',
								'avg_cost',
								'credit_card_accepted',
								'reservation_needed',
								'monday',
								'tuesday',
								'wednesday',
								'thursday',
								'friday',
								'saturday',
								'sunday',
								'food_type',
								'booking_policy',
								'cancellation_policy',
								'guidelines',
								'how_to_reach',
								'latitude',
								'longitude'
							]);

		$latitude_field_name = $basic_form->getElement('latitude');
		$longitude_field_name = $basic_form->getElement('longitude');

	
		$basic_form->add('View_LocationPicker',
							[
								'latitude_field'=>$latitude_field_name,
								'longitude_field'=>$longitude_field_name,
								'lat_value'=>$host_destination['latitude'],
								'lng_value'=>$host_destination['longitude']
							]);
		$basic_form->addSubmit("Update");
		if($basic_form->isSubmitted()){
			$basic_form->save();
			$basic_form->js()->univ()->successMessage("Updated Successfully")->execute();
		}

		$gallary_crud = $image_gallery_tab->add('CRUD');
        $dest_image = $this->add('Model_DestinationImage')
        				->addCondition('destination_id',$host_destination->id)
        				->addCondition('type','destination')
        				->addCondition('status','pending')
        				;

        $dest_image->addHook('afterInsert',function($model)use($host_destination){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new Gallery Image Approved";
			$notification['from_id'] = $host_destination->id;
			$notification['from'] = "Destination";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "image";
			$notification['status'] = "pending";
			$notification['value'] = $model['id'];
			$notification->save();
        });

        $gallary_crud->setModel($dest_image,['image_id','image'],['image']);
        $gallary_crud->grid->addPaginator(10);
        $gallary_crud->grid->addHook('formatRow',function($g){
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

        // Highlight
        $h_tabs = $highlight_tab->add('Tabs');
        $occasion = $h_tabs->addTab('Occasion');
        $facility = $h_tabs->addTab('Facility');
        $service = $h_tabs->addTab('Service');

        //occassion
        $r_hl = $occasion->add('Model_Destination_HighlightAssociation')
        		->addCondition('destination_id',$host_destination->id)
        		->addCondition('highlight_type','occasion')
        		;
        $occ_crud = $occasion->add('CRUD',['entity_name'=>'Occasion']);
        $occ_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $occ_crud->setModel($r_hl,['destination_highlight','destination_highlight_id','destination_id'],['destination_highlight','icon_url']);
        
        if($occ_crud->isEditing()){
        	$form = $occ_crud->form;
        	$form->getElement('destination_highlight_id')->getModel()->addCondition('type','occasion');
        }

        //facility
        $r_hl = $facility->add('Model_Destination_HighlightAssociation')
        		->addCondition('destination_id',$host_destination->id)
        		->addCondition('highlight_type','facility')
        		;
        $fac_crud = $facility->add('CRUD',['entity_name'=>'Facility']);
        $fac_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $fac_crud->setModel($r_hl,['destination_highlight','destination_highlight_id','destination_id'],['destination_highlight','icon_url']);
        if($fac_crud->isEditing()){
        	$form = $fac_crud->form;
        	$form->getElement('destination_highlight_id')->getModel()->addCondition('type','facility');
        }

        //service
        $r_hl = $service->add('Model_Destination_HighlightAssociation')
        		->addCondition('destination_id',$host_destination->id)
        		->addCondition('highlight_type','service')
        		;
        $ser_crud = $service->add('CRUD',['entity_name'=>'Service']);
        $ser_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $ser_crud->setModel($r_hl,['destination_highlight','destination_highlight_id','destination_id'],['destination_highlight','icon_url']);
        if($ser_crud->isEditing()){
        	$form = $ser_crud->form;
        	$form->getElement('destination_highlight_id')->getModel()->addCondition('type','service');
        }


        // Destination Space
		$space_model = $space_tab->add('Model_Destination_Space')->addCondition('destination_id',$host_destination->id);
        $space_crud = $space_tab->add('CRUD');
        $space_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });
        $space_crud->setModel($space_model,['name','cps','size','type','image_id','is_active'],['name','cps','size','type','is_active','icon_url']);

        // Destination_Package
		$package_model = $package_tab->add('Model_Destination_Package')->addCondition('destination_id',$host_destination->id);
        $package_crud = $package_tab->add('CRUD');
        $package_crud->setModel($package_model,['name','price','detail','is_active']);

		// Destination Space
		$venue_model = $category_tab->add('Model_Destination_VenueAssociation')->addCondition('destination_id',$host_destination->id);
        $venue_crud = $category_tab->add('CRUD');
        $venue_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img style='width:100px;' src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });
        $venue_crud->setModel($venue_model,['venue_id','icon_url'],['venue','icon_url']);

	}
}