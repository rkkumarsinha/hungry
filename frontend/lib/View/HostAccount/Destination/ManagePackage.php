<?php

class View_HostAccount_Destination_ManagePackage extends View{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		// Destination_Package
		$host_destination = $this->app->listmodel;
		$package_model = $this->add('Model_Destination_Package')->addCondition('destination_id',$host_destination->id);
        $package_crud = $this->add('CRUD');
        $package_crud->setModel($package_model,['name','price','detail','is_active']);
	}
}