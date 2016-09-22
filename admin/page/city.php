<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_city extends Page {

    public $title='City';

    function init() {
        parent::init();

        $tab = $this->add('Tabs');
        $city_tab = $tab->addTab('City');
        $rest_gallary = $tab->addTab('Restaurant Gallery');
        $event_gallary = $tab->addTab('Event Gallery');
        $venue_gallary = $tab->addTab('Venue Gallery');


        $crud = $city_tab->add('CRUD');
        $crud->setModel('City');
		
		$rest_crud = $rest_gallary->add('CRUD');
        $rest_crud->setModel('City',['name']);
		$rest_crud->grid->add('VirtualPage')
		->addColumn('images')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
		 	
		 	$c = $page->add('CRUD');
		 	$model = $this->add('Model_Image')->addCondition('city_id',$id)->addCondition('type','RestaurantGallery');
		 	$c->setModel($model,['city_id','image_id','image','redirect_url','is_active','app_restaurant_id','app_destination_id','app_event_id'],['city','image','redirect_url','is_active','app_restautant','app_destination','add_event']);

	        $c->grid->addHook('formatRow',function($g){

				if($g->model['image_id']){
					$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
					$f->tryLoadAny();
					if($f->loaded()){
						$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
						$g->current_row_html['image'] = "<img style='max-width:100px;' src=".$path.">";
					}else
						$g->current_row_html['image'] = "No Icon Found";
				}else
					$g->current_row_html['image'] = "No Icon Found";
			}); 	
		});


		$event_crud = $event_gallary->add('CRUD');
        $event_crud->setModel('City',['name']);
		$event_crud->grid->add('VirtualPage')
		->addColumn('images')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
		 	
		 	$c = $page->add('CRUD');
		 	$model = $this->add('Model_Image')->addCondition('city_id',$id)->addCondition('type','EventGallery');
		 	$c->setModel($model,['city_id','image_id','image','redirect_url','is_active','app_restaurant_id','app_destination_id','app_event_id'],['city','image','redirect_url','is_active','app_restautant','app_destination','add_event']);

	        $c->grid->addHook('formatRow',function($g){

				if($g->model['image_id']){
					$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
					$f->tryLoadAny();
					if($f->loaded()){
						$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
						$g->current_row_html['image'] = "<img style='max-width:100px;' src=".$path.">";
					}else
						$g->current_row_html['image'] = "No Icon Found";
				}else
					$g->current_row_html['image'] = "No Icon Found";
			}); 	
		});

		$venue_crud = $venue_gallary->add('CRUD');
        $venue_crud->setModel('City',['name']);
		$venue_crud->grid->add('VirtualPage')
		->addColumn('images')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
		 	
		 	$c = $page->add('CRUD');
		 	$model = $this->add('Model_Image')->addCondition('city_id',$id)->addCondition('type','VenueGallery');
		 	$c->setModel($model,['city_id','image_id','image','redirect_url','is_active','app_restaurant_id','app_destination_id','app_event_id'],['city','image','redirect_url','is_active','app_restautant','app_destination','add_event']);

	        $c->grid->addHook('formatRow',function($g){

				if($g->model['image_id']){
					$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
					$f->tryLoadAny();
					if($f->loaded()){
						$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
						$g->current_row_html['image'] = "<img style='max-width:100px;' src=".$path.">";
					}else
						$g->current_row_html['image'] = "No Icon Found";
				}else
					$g->current_row_html['image'] = "No Icon Found";
			}); 	
		});
    }

}
