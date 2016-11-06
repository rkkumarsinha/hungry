<?php

class View_LocationPicker extends \View{
	public $map_view;
	public $latitude_field = null;
	public $longitude_field = null;
	public $location_field;
	public $radius_field;

	function init(){
		parent::init();

		$form = $this->add('Form');
		$this->location_field = $form->addField('location');
		$this->radius_field = $form->addField('Number','radius');
		// $this->latitude_field = $form->addField('latitude');
		// $this->longitude_field = $form->addField('longitude');

		$this->map_view = $this->add('View')->setStyle('height','400px');
		$this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=false&libraries=places&key='.$this->api->getConfig('Google/MapKey'));
		$this->js(true)->_load('locationpicker.jquery');
		$this->js(true)->_load('hungry');
	}


	function render(){

		$this->js(true)->univ()->hungryLocationPicker(
									$this->map_view->name,
									24.585445,
									73.71247900000003,
									// $this->latitude_field->name,
									// $this->longitude_field->name,
									$this->latitude_field,
									$this->longitude_field,
									$this->location_field->name,
									$this->radius_field->name
								);

		parent::render();
	}
}