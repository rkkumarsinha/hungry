<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_notification extends Page {

    public $title='Notification';

    function init() {
        parent::init();

        $tabs = $this->add('Tabs');
		$admin_tab = $tabs->addTab('Admin Alert');
		$rest_tab = $tabs->addTab('For Restaurant');
		$event_tab = $tabs->addTab('For Event');
		$destination_tab = $tabs->addTab('For Destination');


        $crud = $admin_tab->add('Grid');
        $crud->setModel('Notification');
        $crud->addPaginator($ipp=50);
        $crud->addQuickSearch(['name','created_at','message','from','request_for','status']);
		
		// Restaurant Form
		$form = $rest_tab->add('Form',null,null,['form/stacked']);
		$col = $form->add('Columns');
		$country_col = $col->addColumn(4);
		// $state_col = $col->addColumn(3);
		// $city_col = $col->addColumn(3);
		// $rest_col = $col->addColumn(3);
		// $name_col = $col->addColumn(12);
		$message_col = $col->addColumn(8);

		$r_country_field  = $country_col->addField('DropDown','country');
		$r_country_field->setModel($this->add('Model_Country')->addCondition('is_active',true));
		
		$r_state_field = $country_col->addField('DropDown','state');
		$r_state_field->setModel($this->add('Model_State')->addCondition('is_active',true));
				
		$r_city_field = $country_col->addField('DropDown','city');
		$r_city_field->setModel($this->add('Model_City')->addCondition('is_active',true));
		$r_city_field->setEmptyText('All City');

		$r_restaurant_field = $country_col->addField('DropDown','restaurant');
		$r_restaurant_field->setModel($this->add('Model_Restaurant')->addCondition('status','active'));
		$r_restaurant_field->setEmptyText('All Restaurant');

		$message_col->addField('name')->validateNotNull();
		$message_col->addField('RichText','message')->validateNotNull();

		$message_col->add('Button')->set('Submit')->js('click',$form->js()->submit());

			
		$r_notification_model = $this->add('Model_Notification');
		$r_notification_model->addCondition('from','HungryDunia');
		$r_notification_model->addCondition('to','Restaurant');


		$r_notification_model->setOrder('created_at','desc');

		$rest_crud = $rest_tab->add('CRUD',['allow_add'=>false,'allow_edit'=>false]);
		$rest_crud->setModel($r_notification_model,['name','message'],['name','message','created_at','to_id']);
		$rest_crud->grid->addPaginator($ipp=10);

		$rest_crud->grid->addHook('formatRow',function($g){
			if($g->model['to_id'] and $g->model['to'] = "Restaurant"){
				$g->current_row_html['to_id'] = $this->add('Model_Restaurant')->tryLoad($g->model['to_id'])->get('name');
			}else
				$g->current_row_html['to_id'] = "All";
			$g->current_row_html['message'] = $g->model['message'];
		});

		if($form->isSubmitted()){
			$notification_model = $this->add('Model_Notification');
			$notification_model['from'] = "HungryDunia";
			$notification_model['to'] = 'Restaurant';
			if($form['restaurant'])
				$notification_model['to_id'] = $form['restaurant'];

			$notification_model['country_id'] = $form['country'];
			$notification_model['state_id'] = $form['state'];
			$notification_model['city_id'] = $form['city'];

			$notification_model['name'] = $form['name'];
			$notification_model['message'] = $form['message'];
			$notification_model->save();

			$form->js(null,$rest_crud->js()->reload(['restaurant_id'=>$form['restaurant']]))->univ()->successMessage('Notify Successfully')->execute();
		}

		// Event Form
		$event_form = $event_tab->add('Form',null,null,['form/stacked']);
		$col = $event_form->add('Columns');
		$country_col = $col->addColumn(4);
		// $state_col = $col->addColumn(3);
		// $city_col = $col->addColumn(3);
		// $rest_col = $col->addColumn(3);
		// $name_col = $col->addColumn(12);
		$message_col = $col->addColumn(8);

		$r_country_field  = $country_col->addField('DropDown','country');
		$r_country_field->setModel($this->add('Model_Country')->addCondition('is_active',true));
		
		$r_state_field = $country_col->addField('DropDown','state');
		$r_state_field->setModel($this->add('Model_State')->addCondition('is_active',true));
				
		$r_city_field = $country_col->addField('DropDown','city');
		$r_city_field->setModel($this->add('Model_City')->addCondition('is_active',true));
		$r_city_field->setEmptyText('All City');

		$r_restaurant_field = $country_col->addField('DropDown','event');
		$r_restaurant_field->setModel($this->add('Model_Event')->addCondition('is_active','true'));
		$r_restaurant_field->setEmptyText('All Event');

		$message_col->addField('name')->validateNotNull();
		$message_col->addField('RichText','message')->validateNotNull();

		$message_col->add('Button')->set('Submit')->js('click',$event_form->js()->submit());

			
		$e_notification_model = $event_tab->add('Model_Notification');
		$e_notification_model->addCondition('from','HungryDunia');
		$e_notification_model->addCondition('to','Event');
		$e_notification_model->setOrder('created_at','desc');

		$event_crud = $event_tab->add('CRUD',['allow_add'=>false,'allow_edit'=>false]);
		$event_crud->setModel($e_notification_model,['name','message'],['name','message','created_at','to_id']);
		$event_crud->grid->addPaginator($ipp=10);

		$event_crud->grid->addHook('formatRow',function($g){
			if($g->model['to_id'] and $g->model['to'] = "Event"){
				$g->current_row_html['to_id'] = $this->add('Model_Event')->tryLoad($g->model['to_id'])->get('name');
			}else
				$g->current_row_html['to_id'] = "All";
			$g->current_row_html['message'] = $g->model['message'];
		});

		if($event_form->isSubmitted()){
			$notification_model = $this->add('Model_Notification');
			$notification_model['from'] = "HungryDunia";
			$notification_model['to'] = 'Event';
			if($event_form['event'])
				$notification_model['to_id'] = $event_form['event'];

			$notification_model['country_id'] = $event_form['country'];
			$notification_model['state_id'] = $event_form['state'];
			$notification_model['city_id'] = $event_form['city'];

			$notification_model['name'] = $event_form['name'];
			$notification_model['message'] = $event_form['message'];
			$notification_model->save();

			$event_form->js(null,$event_crud->js()->reload())->univ()->successMessage('Notify Successfully')->execute();
		}

		// Destination
		$destination_form = $destination_tab->add('Form',null,null,['form/stacked']);
		$col = $destination_form->add('Columns');
		$country_col = $col->addColumn(4);
		// $state_col = $col->addColumn(3);
		// $city_col = $col->addColumn(3);
		// $rest_col = $col->addColumn(3);
		// $name_col = $col->addColumn(12);
		$message_col = $col->addColumn(8);

		$r_country_field  = $country_col->addField('DropDown','country');
		$r_country_field->setModel($this->add('Model_Country')->addCondition('is_active',true));
		
		$r_state_field = $country_col->addField('DropDown','state');
		$r_state_field->setModel($this->add('Model_State')->addCondition('is_active',true));
				
		$r_city_field = $country_col->addField('DropDown','city');
		$r_city_field->setModel($this->add('Model_City')->addCondition('is_active',true));
		$r_city_field->setEmptyText('All City');

		$r_restaurant_field = $country_col->addField('DropDown','destination');
		$r_restaurant_field->setModel($this->add('Model_Destination')->addCondition('status','active'));
		$r_restaurant_field->setEmptyText('All Destination');

		$message_col->addField('name')->validateNotNull();
		$message_col->addField('RichText','message')->validateNotNull();

		$message_col->add('Button')->set('Submit')->js('click',$destination_form->js()->submit());

			
		$d_notification_model = $destination_tab->add('Model_Notification');
		$d_notification_model->addCondition('from','HungryDunia');
		$d_notification_model->addCondition('to','Destination');
		$d_notification_model->setOrder('created_at','desc');

		$destination_crud = $destination_tab->add('CRUD',['allow_add'=>false,'allow_edit'=>false]);
		$destination_crud->setModel($d_notification_model,['name','message'],['name','message','created_at','to_id']);
		$destination_crud->grid->addPaginator($ipp=10);

		$destination_crud->grid->addHook('formatRow',function($g){
			if($g->model['to_id'] and $g->model['to'] = "Event"){
				$g->current_row_html['to_id'] = $this->add('Model_Destination')->tryLoad($g->model['to_id'])->get('name');
			}else
				$g->current_row_html['to_id'] = "All";
			$g->current_row_html['message'] = $g->model['message'];
		});

		if($destination_form->isSubmitted()){
			$notification_model = $this->add('Model_Notification');
			$notification_model['from'] = "HungryDunia";
			$notification_model['to'] = 'Destination';
			if($destination_form['destination'])
				$notification_model['to_id'] = $destination_form['destination'];

			$notification_model['country_id'] = $destination_form['country'];
			$notification_model['state_id'] = $destination_form['state'];
			$notification_model['city_id'] = $destination_form['city'];

			$notification_model['name'] = $destination_form['name'];
			$notification_model['message'] = $destination_form['message'];
			$notification_model->save();

			$destination_form->js(null,$destination_crud->js()->reload())->univ()->successMessage('Notify Successfully')->execute();
		}
    }
}
