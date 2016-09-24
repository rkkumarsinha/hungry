<?php

class Model_Image extends SQL_Model{
	public $table = "image";

	function init(){
		parent::init();

		$this->hasOne('Restaurant');
		$this->hasOne('Destination');
		$this->hasOne('City','city_id');
		$this->hasOne('Event','event_id');
		$this->hasOne('Area','area_id');

		$this->hasOne('Restaurant','app_restaurant_id')->hint("used for app when, when clicking on slider images redirect to restaurant detail view");
		$this->hasOne('Destination','app_destination_id')->hint("used for app when, when clicking on slider images redirect to destination detail view");
		$this->hasOne('Event','app_event_id')->hint("used for app when, when clicking on slider images redirect to event detail view");

		$this->addField('name');
		$this->addField('redirect_url');
		$this->addField('is_active')->type('boolean')->defaultValue(false);

		$this->addField('type')->setValueList(['restaurant'=>'restaurant','menu'=>'menu','city'=>'city','event'=>'event','destination'=>'destination','RestaurantGallery'=>"RestaurantGallery",'EventGallery'=>"EventGallery",'VenueGallery'=>"VenueGallery"])->mandatory(true);
		$this->add('filestore/Field_File','image_id')->mandatory(true);

		$this->addField('status')->setValueList(['pending'=>"Pending",'approved'=>"Approved",'cancled'=>"Cancled"]);
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('approved_date')->type('datetime');//->defaultValue(date('Y-m-d H:i:s'));

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

}