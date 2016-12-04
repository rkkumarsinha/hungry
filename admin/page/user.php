<?php

class page_user extends Page{
	function init(){
		parent::init();

		$tab = $this->add('Tabs');
		$user_tab = $tab->addTab('User');
		$host_tab = $tab->addTab('Host');
		$admin_tab = $tab->addTab('Admin');
		$super_admin_tab = $tab->addTab('Super Admin');


		$user_model = $this->add('Model_User')->addCondition('type','user');
		$user_crud = $user_tab->add('CRUD');
		$user_crud->setModel($user_model);
		$user_crud->grid->addPaginator($ipp=30);
		$user_crud->grid->addQuickSearch(['name','email']);
		$user_model->setOrder('created_at','Desc');


		$host_model = $this->add('Model_User')->addCondition('type','host');
		$host_model->setOrder('created_at','desc');
		$host_crud = $host_tab->add('CRUD');
		$host_crud->setModel($host_model,['name','email','type','verification_code','password']);
		$host_crud->grid->addPaginator($ipp=30);
	

		$admin_model = $this->add('Model_User')->addCondition('type','admin');
		$admin_model->setOrder('created_at','desc');
		$admin_crud = $admin_tab->add('CRUD');
		$admin_crud->setModel($admin_model,['name','email','type','verification_code','password']);
		$admin_crud->grid->addPaginator($ipp=30);

		$sadmin_model = $this->add('Model_User')->addCondition('type','superadmin');
		$sadmin_model->setOrder('created_at','desc');
		$sadmin_crud = $sadmin_model->add('CRUD');
		$sadmin_crud->setModel($sadmin_model,['name','email','type','verification_code','password']);
		$sadmin_crud->grid->addPaginator($ipp=30);

	}
}