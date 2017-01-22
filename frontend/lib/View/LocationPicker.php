<?php

class View_LocationPicker extends \View{
	public $map_view;
	public $latitude_field = null;
	public $longitude_field = null;
	public $location_field;
	public $radius_field;
	public $lat_value = 0;
	public $lng_value = 0;


	function init(){
		parent::init();

		$form = $this->owner;
		// $form = $this->add('Form');
		$this->location_field = $form->addField('location');
		$this->radius_field = $form->addField('hidden','radius');
		// $this->latitude_field = $form->addField('latitude');
		// $this->longitude_field = $form->addField('longitude');
		$view = $form->add('View_Box');
		$this->map_view = $view->add('View')
						->setStyle('height','400px')
						;
		// $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&libraries=places&key='.$this->api->getConfig('Google/MapKey'));
		// $this->app->jui->addStaticInclude('locationpicker.jquery');
		// $this->app->jui->addStaticInclude('hungry');
		// $this->js(true)->_load('locationpicker.jquery');
		// $this->js(true)->_load('hungry');
	}


	function render(){
		if(!$this->lng_value) $this->lng_value = 73.71247900000003;
		if(!$this->lat_value) $this->lat_value = 24.585445;

		$this->js(true)->univ()->hungryLocationPicker(
									$this->map_view->name,
									$this->lat_value,
									$this->lng_value,
									$this->latitude_field->name,
									$this->longitude_field->name,
									$this->location_field->name,
									$this->radius_field->name
								);

		parent::render();
	}
}