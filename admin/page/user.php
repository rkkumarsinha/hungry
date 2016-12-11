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
		$user_model->addExpression('register_via')->set(function($m,$q){
			$ac = $m->add('Model_AccessToken',['table_alias'=>'hungry_access_token']);
			return $ac->addCondition('user_id',$q->getField('id'))
						->_dsql()->del('fields')->field($q->expr('group_concat([0] SEPARATOR "<br/>")',[$ac->getElement('social_app')]));
		})->allowHTML(true)->sortable(true);

		$user_crud = $user_tab->add('CRUD');
		$user_crud->add('misc/Export');
		$user_crud->setModel($user_model);
		$user_crud->grid->addPaginator($ipp=30);
		$user_crud->grid->addQuickSearch(['name','email']);
		$user_model->setOrder('created_at','Desc');
		$user_crud->grid->addHook('formatRow',function($g){

			if($g->model['profile_image_url']){
				$g->current_row_html['profile_image_url'] = '<img width="100px;" src="'.$g->model['profile_image_url'].'"/>';
			}else
				$g->current_row_html['profile_image_url'] = $g->model['profile_image_url'];
		});

		$host_model = $this->add('Model_User')->addCondition('type','host');
		$host_model->setOrder('created_at','desc');
		$host_crud = $host_tab->add('CRUD');
		$host_crud->setModel($host_model,['name','email','type','verification_code','password','is_verified','is_active']);
		$host_crud->grid->addPaginator($ipp=30);
		$host_crud->add('misc/Export');

		$admin_model = $this->add('Model_User')->addCondition('type','admin');
		$admin_model->setOrder('created_at','desc');
		$admin_crud = $admin_tab->add('CRUD');
		$admin_crud->setModel($admin_model,['name','email','type','verification_code','password','is_verified','is_active']);
		$admin_crud->grid->addPaginator($ipp=30);

		$sadmin_model = $this->add('Model_User')->addCondition('type','superadmin');
		$sadmin_model->setOrder('created_at','desc');
		$sadmin_crud = $sadmin_model->add('CRUD');
		$sadmin_crud->setModel($sadmin_model,['name','email','type','verification_code','password','is_verified','is_active']);
		$sadmin_crud->grid->addPaginator($ipp=30);

	}
}