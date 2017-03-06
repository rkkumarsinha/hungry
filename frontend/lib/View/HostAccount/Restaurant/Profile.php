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
		$meta_tab = $tab->addTab('Meta Info');
		
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
								'disclaimer',
								'address',
								'mobile_no',
								'phone_no',
								'email',
								'website',
								'facebook_page_url',
								'instagram_page_url',
								'monday',
								'tuesday',
								'wednesday',
								'thursday',
								'friday',
								'saturday',
								'sunday',
								'food_type',
								'avg_cost_per_person_veg',
								'avg_cost_per_person_nonveg',
								'avg_cost_per_person_thali',
								'avg_cost_of_a_beer',
								'credit_card_accepted',
								'reservation_needed',
								'discount_id',
								// 'latitude',
								// 'longitude'
							]);

		
		$discount_field = $basic_form->getElement('discount_id');
		$discount_field->setCaption('Discount %');
		$basic_form->addField('Readonly','operational_cost')->set('5 %');
		
		// $rest_model =$this->add('Model_Restaurant')->load($host_restaurant['id']);

		$discount_to_the_customer_field = $basic_form->add('View')->setElement('div')->addClass('atk-form-row atk-cells atk-push-small atk-form-row-readonly')
							->setHtml('<div class="atk-cell atk-form-label atk-text-nowrap"><label for="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost"><span>Discount To The Customer :</span></label></div><div class="atk-cell atk-form-field atk-jackscrew "><div id="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost" name="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost" data-shortname="operational_cost" class="atk-form-field-readonly" disabled="true">'.($host_restaurant['discount'] - $host_restaurant['discount_subtract']?:5).' % </div></div>');
		// $discount_to_the_customer_field = $basic_form->addField('Readonly','discount_to_the_customer');

		$this->api->stickyGET('selected_discount_id');
		$this->api->stickyGET('discount_subtract');
		if($_GET['selected_discount_id']){
			$discount_subtract = $_GET['discount_subtract']?:5;
			$discount_model = $this->add('Model_Discount')->load($_GET['selected_discount_id']);

			$discount_given = $discount_model['name'] - $discount_subtract;
			$discount_to_the_customer_field
				->setElement('div')->addClass('atk-form-row atk-cells atk-push-small atk-form-row-readonly')
				->setHtml('<div class="atk-cell atk-form-label atk-text-nowrap"><label for="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost"><span>Discount To The Customer:</span></label></div><div class="atk-cell atk-form-field atk-jackscrew "><div id="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost" name="8f7602b6__rofile_tabs_view_htmlelement_form_operational_cost" data-shortname="operational_cost" class="atk-form-field-readonly" disabled="true">'.$discount_given.' % </div></div>');
		}

		$discount_field->js('change',
							$discount_to_the_customer_field->js()->reload(null,null,
																[
																	$this->app->url(null,['cut_object'=>$discount_to_the_customer_field->name,'discount_subtract'=>$host_restaurant['discount_subtract']]),
																	'selected_discount_id'=>$discount_field->js()->val()
																	]));
		// $discount_field->js('change',$this->js()->atk4_form('reloadField','discount_to_the_customer',[$this->app->url(),'selected_discount_id'=>$discount_to_the_customer_field->js()->val()]));
		$latitude_field_name = $basic_form->addField('latitude')->set($host_restaurant['latitude']);
		$longitude_field_name = $basic_form->addField('longitude')->set($host_restaurant['longitude']);

		// $latitude_field_name = $basic_form->getElement('latitude');
		// $longitude_field_name = $basic_form->getElement('longitude');
		$basic_form->add('View_LocationPicker',
							[
								'latitude_field'=>$latitude_field_name,
								'longitude_field'=>$longitude_field_name,
								'lat_value'=>$host_restaurant['latitude'],
								'lng_value'=>$host_restaurant['longitude']
							]);

		$basic_form->addSubmit("Update");
		if($basic_form->isSubmitted()){
			// $basic_form->save();
			$temp_model = $basic_form->model;
			$temp_model['latitude'] = $basic_form['latitude'];
			$temp_model['longitude'] = $basic_form['longitude'];
			// $temp_model->save();
			$basic_form->save();

			$basic_form->js()->univ()->successMessage("Updated Successfully")->execute();
		}

		$crud = $image_gallery_tab->add('CRUD');
        $rest_image = $this->add('Model_RestaurantImage')
        				->addCondition('restaurant_id',$host_restaurant->id)
        				->addCondition('type','restaurant')
        				// ->addCondition('status',['pending','app'])
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

        $crud->setModel($rest_image,['image_id','image'],['image','status']);
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
        				// ->addCondition('status','pending')
        				;

        $menu_image->addHook('afterInsert',function($model)use($host_restaurant){
			$notification = $this->add('Model_Notification');
			$notification['name'] = "Request for new Menu Image Approved";
			$notification['from_id'] = $host_restaurant->id;
			$notification['from'] = "Restaurant";
			$notification['to'] = "HungryDunia";
			$notification['request_for'] = "image";
			$notification['status'] = "pending";
			$notification['value'] = $model['id'];
			$notification->save();
        });

        $menu_crud->setModel($menu_image,['image_id','image'],['image','status']);
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

        //highlight
        $r_hl = $highlight_tab->add('Model_Restaurant_Highlight')->addCondition('restaurant_id',$host_restaurant->id);
		$r_hl->addExpression('image')->set($r_hl->refSQL('Highlight_id')->fieldQuery('image_id'));

        $hl_crud = $highlight_tab->add('CRUD');
        $hl_crud->grid->addHook('formatRow',function($g){
        	$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image']);
            $f->tryLoadAny();
            if($f->loaded()){
                $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                $g->current_row_html['image'] = "<img width='50px' src=".$path.">";
            }else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $hl_crud->setModel($r_hl,['Highlight_id','image'],['Highlight','image']);

        //Restaurant keyword
        $cu_hl = $cuisine_tab->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$host_restaurant->id);
		$cu_hl->addExpression('image')->set($cu_hl->refSQL('keyword_id')->fieldQuery('image_id'));
        $cu_crud = $cuisine_tab->add('CRUD');
        $cu_crud->grid->addHook('formatRow',function($g){
           	$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image']);
            $f->tryLoadAny();
            if($f->loaded()){
                $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                $g->current_row_html['image'] = "<img width='50px' src=".$path.">";
            }else
                $g->current_row_html['image'] = "No Icon Found";

        });
        $cu_crud->setModel($cu_hl,['keyword_id','image'],['keyword','image']);
		
		// Category Association
		$category_tab->add('View_Info')->set('add maximum 6 category');
		$cat_form = $category_tab->add('Form');
		$cat_field = $cat_form->addField('hidden','cat_ids');

		$cat_asso = $this->add('Model_CategoryAssociation');
	    $cat_asso->addCondition('restaurant_id',$host_restaurant->id);
	    $cat_asso = $cat_asso->_dsql()->del('fields')->field('category_id')->getAll();
		$associated_cat = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($cat_asso)),false);

		$cat_field->set(json_encode($associated_cat));

        $cat_crud = $category_tab->add('Grid');
        $cat_crud->addHook('formatRow',function($g){
           	$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
            $f->tryLoadAny();
            if($f->loaded()){
                $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                $g->current_row_html['image'] = "<img width='50px' src=".$path.">";
            }else
                $g->current_row_html['image'] = "No Icon Found";

        });
        $cat_crud->addSelectable($cat_field);

        $cat_crud->setModel($this->add('Model_Category'));
        $category_tab->add('Button')->set('Save')->js('click',$cat_form->js()->submit());
        // $cat_form->addSubmit('Save');
        if($cat_form->isSubmitted()){
        	$cat_array = json_decode($cat_form['cat_ids']);
        	if(count($cat_array) > 6){
        		$cat_form->js()->univ()->alert('can\'t add more then 6 category')->execute();
        	}
   			
   			$cat_asso = $this->add('Model_CategoryAssociation');
	        $cat_asso->addCondition('restaurant_id',$host_restaurant->id);
	        // $cat_asso->addExpression('image')->set($cat_asso->refSQL('category_id')->fieldQuery('image_id'));
	        if($cat_asso->count()->getOne()){
	        	$cat_asso->deleteAll();
	        }

   			foreach ($cat_array as $key => $cat_id) {
				$cat_asso = $this->add('Model_CategoryAssociation');
	        	$cat_asso->addCondition('restaurant_id',$host_restaurant->id);
	        	$cat_asso->addCondition('category_id',$cat_id);
   				$cat_asso->tryLoadAny();
   				$cat_asso->save();
   			}     	
   			$cat_form->js()->univ()->successMessage('Saved Successfully')->execute();
        }
        // $cat_asso = $this->add('Model_CategoryAssociation');
        // $cat_asso->addCondition('restaurant_id',$host_restaurant->id);
        // $cat_asso->addExpression('image')->set($cat_asso->refSQL('category_id')->fieldQuery('image_id'));
        // $cat_crud->setModel($cat_asso,['category','image']);//,['category','image']);

        // Meta Forms
        $meta_form = $meta_tab->add('Form',null,null,['form/stacked']);
        $meta_form->setModel($host_restaurant,['title','keyword','description','image_title','image_alt_text']);
 		$meta_form->addSubmit('Save');
 		if($meta_form->isSubmitted()){
 			$meta_form->save();
 			$meta_form->js(null,$meta_form->js()->reload())->univ()->successMessage("Information Saved Successfully")->execute();
 		}

	}
}