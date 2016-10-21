<?php

class View_HostAccount_Restaurant_Profile extends View{
	function init(){
		parent::init();
	
		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;

		$tab = $this->add('Tabs');
		$basic_info_tab = $tab->addTab('Basic Info');
		$image_gallery_tab = $tab->addTab('Image Gallery');
		$menu_tab = $tab->addTab('Menu');
		$highlight_tab = $tab->addTab('Highlight');
		$cuisine_tab = $tab->addTab('Cuisine');
		$category_tab = $tab->addTab('Category');
		
		$basic_form = $basic_info_tab->add('Form');
		$basic_form->setModel($host_restaurant,
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
								'about_restaurant',
								'address',
								'mobile_no',
								'phone_no',
								'email',
								'website',
								'facebook_page_url',
								'instagram_page_url',
								'rating',
								'avg_cost_per_person_veg',
								'avg_cost_per_person_nonveg',
								'avg_cost_per_person_thali',
								'avg_cost_of_a_beer',
								'credit_card_accepted',
								'reservation_needed',
								'type',
								'longitude',
								'latitude',
								'monday',
								'tuesday',
								'wednesday',
								'thursday',
								'friday',
								'saturday',
								'sunday',
								'food_type'
							]);

		$basic_form->addSubmit("Update");
		if($basic_form->isSubmitted()){
			$basic_form->save();
			$basic_form->js()->univ()->successMessage("Updated Successfully");
		}

		$crud = $image_gallery_tab->add('CRUD');
        $rest_image = $this->add('Model_RestaurantImage')
        				->addCondition('restaurant_id',$host_restaurant->id)
        				->addCondition('type','restaurant')
        				->addCondition('status','pending')
        				;

        $rest_image->addHook('afterInsert',function($model)use($host_restaurant){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new Gallery Image Approved";
			$notification['from_id'] = $host_restaurant->id;
			$notification['from'] = "Restaurant";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "image";
			$notification['status'] = "pending";
			$notification['value'] = $model['id'];
			$notification->save();
        });

        $crud->setModel($rest_image,['image_id','image'],['image']);
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

// menu image

        $menu_crud = $menu_tab->add('CRUD');
        $menu_image = $this->add('Model_RestaurantMenu')
        				->addCondition('restaurant_id',$host_restaurant->id)
        				->addCondition('type','menu')
        				->addCondition('status','pending')
        				;

        $menu_image->addHook('afterInsert',function($model)use($host_restaurant){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new Gallery Image Approved";
			$notification['from_id'] = $host_restaurant->id;
			$notification['from'] = "Restaurant";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "image";
			$notification['status'] = "pending";
			$notification['value'] = $model['id'];
			$notification->save();
        });

        $menu_crud->setModel($menu_image,['image_id','image'],['image']);
        $menu_crud->grid->addPaginator(10);
        $menu_crud->grid->addHook('formatRow',function($g){
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

        $r_hl = $highlight_tab->add('Model_Restaurant_Highlight')->addCondition('restaurant_id',$host_restaurant->id);
        $hl_crud = $highlight_tab->add('CRUD');
        $hl_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $hl_crud->setModel($r_hl);

        $cu_hl = $cuisine_tab->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$host_restaurant->id);
        $cu_crud = $cuisine_tab->add('CRUD');
        $cu_crud->setModel($cu_hl);
        $cu_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });

        
        $cat_crud = $category_tab->add('CRUD');
        $cat_asso = $this->add('Model_CategoryAssociation');
        $cat_asso->addCondition('restaurant_id',$host_restaurant->id);
        $cat_crud->setModel($cat_asso);

	}
}