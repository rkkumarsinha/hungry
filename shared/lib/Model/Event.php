<?php

class Model_Event extends SQL_Model{
	public $table = "event";

	function init(){
		parent::init();

		$this->hasOne('User','user_id'); // actual host

		$this->hasOne('Country','country_id')->mandatory(true);
		$this->hasOne('State','state_id')->mandatory(true);
		$this->hasOne('City','city_id')->mandatory(true);
		$this->hasOne('Area','area_id')->mandatory(true);
		$this->hasOne('Event_Category','event_category_id');

		$this->add('filestore/Field_File','logo_image_id'); //for detail
		$this->add('filestore/Field_File','banner_image_id'); //for detail
		$this->add('filestore/Field_File','display_image_id'); //for list

		$this->addField('name')->mandatory(true)->caption('Event Name');
		$this->addField('owner_name')->mandatory(true);
		$this->addField('detail')->type('text')->mandatory(true);
		$this->addField('address')->type('text');
		$this->addField('mobile_no')->hint('Comma separated multiple value');
		$this->addField('phone_no');
		$this->addField('email')->hint('Comma separated multiple value');
		$this->addField('website');
		$this->addField('event_attraction')->type('text')->mandatory(true);
		$this->addField('facebook_page_url');
		$this->addField('instagram_page_url');

		$this->addField('is_featured')->type('boolean')->defaultValue(0);

		$this->addField('starting_date')->type('date')->defaultValue(date('Y-m-d H:i:s'))->mandatory(true);
		$this->addField('starting_time')->defaultValue(date('H:i:s'))->mandatory(true);
		$this->addField('closing_date')->type('date')->defaultValue(date('Y-m-d H:i:s'))->mandatory(true);
		$this->addField('closing_time')->defaultValue(date('H:i:s'))->mandatory(true);

		$this->addField('longitude')->mandatory(true);
		$this->addField('latitude')->mandatory(true);

		$this->addField('guidelines')->type('text')->mandatory(true);
		$this->addField('how_to_reach')->type('text')->mandatory(true);
		$this->addField('disclaimer')->type('text')->mandatory(true);
		//slug URL
		$this->addField('url_slug');
	
		$this->addField('is_active')->type('boolean')->defaultValue(false);
		$this->addField('is_verified')->type('boolean')->defaultValue(false);

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		
		// SEO Field
		$this->addField('title')->type('text')->hint('Ex: Best Event in Udaipur - Event Name');
		$this->addField('keyword')->type('text')->hint('Ex: best event, event in udaipur etc.');
		$this->addField('description')->type('text')->hint('Short description about your event');
		$this->addField('image_title')->type('text')->hint('Ex:Event in Udaipur - Event Name');
		$this->addField('image_alt_text')->type('text')->hint('Ex:event udaipur');

		$this->hasMany('Event_Day','event_id');
		$this->hasMany('Event_Ticket','event_id');
		$this->hasMany('EventImage','event_id');
		$this->hasMany('EventDestinationRest','event_id');

		$this->addExpression('total_day')->set(function($m,$q){
			return $m->refSQL('Event_Day')->count();
		});

		$this->addExpression('remaining_tickets')->set($this->refSQL('Event_Ticket')->sum('remaining_ticket'));

		$this->addExpression('lowest_price')->set(function($m,$q){
			return $m->refSQL('Event_Ticket')->setOrder('price','asc')->setLimit(1)->fieldQuery('price');
		});

		$this->addExpression('category_icon_url')->set(function($m,$q){
			// $q->expr("replace([0],'/public','')",[$m->refSQL('category_id')->fieldQuery('image')]);
			return $m->refSQL('event_category_id')->fieldQuery('image_id');
		});

		$this->addField('search_string')->type('text')->system(true)->defaultValue(null);

		$this->addHook('beforeSave',[$this,'beforeSave']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);
		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function beforeSave(){
		
		// if($this['starting_date'] < $this->app->today){
		// 	throw $this->exception('Event cannot be add on previous day', 'ValidityCheck')->setField('starting_date');
		// }

		if($this['starting_date'] > $this['closing_date']){
			throw $this->exception('closing day must be greater then starting day', 'ValidityCheck')->setField('closing_date');
		}

		if(!$this['url_slug'])
			$this['url_slug'] = implode("-", explode(" ", $this['name']))."-".$this['city']."-HungryDunia";
	}

	function updateSearchString(){	
		if(!$this->loaded())
			return;
		$search_string = ' ';
		$search_string .=" ".$this['name'];
		$search_string .=" ".$this['address'];
		$search_string .=" ".$this['event_attraction'];
		$search_string .=" ".$this['city'];
		$search_string .=" ".$this['area'];
		$search_string .=" ".$this['state'];
		$search_string .=" ".$this['country'];
		$search_string .=" ".$this['id'];

		// $this->hasMany('Event_Day','event_id');
		// $this->hasMany('Event_Ticket','event_id');
		// $this->hasMany('EventDestinationRest','event_id');

		// // CategoryAssociation
		// $categoryfields = $this->add('Model_CategoryAssociation')->addCondition('restaurant_id',$this->id);
		// foreach ($categoryfields as $all_categoryfields) {
		// 	$search_string .=" ". $all_categoryfields['category'];
		// }
		$this['search_string'] = $search_string;
		$this->save();
	}

	function getImage(){
		if(!$this->loaded())
			throw new \Exception("something wrong 10009", 1);

		$images_model = $this->ref('EventImage')->addCondition('is_active',true);

		$output = [];
		foreach ($images_model as $image) {
			$output[] = ['name'=>$image['name'],'url'=>$image['image']];
		}

		return $output;
	}

	function getDestination(){
		if(!$this->loaded())
			throw new \Exception("something wrong 10009", 1);

		$dest_asso = $this->add('Model_EventDestinationRest')
						->addCondition('event_id',$this->id)
						->addCondition('destination_id','<>',"null")
						;

		$output = [];
		foreach ($dest_asso as $dest) {
			$output[] = ['name'=>$dest['destination'],'id'=>$dest['destination_id']];
		}

		return $output;
				
	}

	function getRestaurant(){

		$rest_asso = $this->add('Model_EventDestinationRest')
						->addCondition('event_id',$this->id)
						->addCondition('restaurant_id','<>',"null")
						;

		$output = [];
		foreach ($rest_asso as $rest) {
			$output[] = ['name'=>$rest['restaurant'],'id'=>$rest['restaurant_id']];
		}

		return $output;
			
	}

	function getDayTime(){
		if(!$this->loaded())
			return array('event model must loaded');

		$model_day = $this->add('Model_Event_Day')->addCondition('event_id',$this->id)->getRows();
		$output = [];
		foreach ($model_day as $day) {

			$time_array = [];
			$ticket_array = [];
			$model_time = $this->add('Model_Event_Time')->addCondition('event_day_id',$day['id'])->addCondition('event_id',$this->id)->getRows();

			foreach ($model_time as $time) {

				$model_ticket = $this->add('Model_Event_Ticket')
									->addCondition('event_time_id',$time['id'])
									->addCondition('event_id',$this->id)
									->getRows();
				foreach ($model_ticket as $ticket) {
					$ticket_array[] = [
										'id'=>$ticket['id'],
										'name'=>$ticket['name'],
										'price'=>$ticket['price'],
										'detail'=>$ticket['detail'],
										'offer'=>$ticket['offer'],
										'applicable_offer_qty'=>$ticket['applicable_offer_qty'],
										'offer_percentage'=>$ticket['offer_percentage'],
										'remaining_ticket'=>$ticket['remaining_ticket']
									];
				}

				$time_array[] = ["id"=>$time['id'],"name"=>$time['name'],'ticket'=>$ticket_array];
			}


			$output[] = [
							'id'=>$day['id'],
							'name'=>$day['name'],
							'date'=>$day['on_date'],
							"time"=>$time_array
						];
		}
		return $output;
	}
}