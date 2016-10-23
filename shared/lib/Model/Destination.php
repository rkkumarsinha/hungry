<?php

class Model_Destination extends SQL_Model{
	public $table = "destination";

	function init(){
		parent::init();

		$this->hasOne('User','user_id'); // actuall host
		$this->hasOne('Country','country_id')->mandatory(true);
		$this->hasOne('State','state_id')->mandatory(true);
		$this->hasOne('City','city_id')->mandatory(true);
		$this->hasOne('Area','area_id')->mandatory(true);

		$this->addExpression('city')->set(function($m,$q){
			return $m->refSQL('area_id')->fieldQuery('city');
		});

		$this->add('filestore/Field_Image','logo_image_id');
		$this->add('filestore/Field_Image','banner_image_id'); //for detail
		$this->add('filestore/Field_Image','display_image_id'); //for list

		$this->addField('name')->mandatory(true);
		$this->addField('owner_name')->mandatory(true);
		$this->addField('about_destination')->type('text')->mandatory(true);
		$this->addField('address')->type('text');
		$this->addField('mobile_no')->hint('Comma separated multiple value');
		$this->addField('phone_no');
		$this->addField('email')->hint('Comma separated multiple value');
		$this->addField('website');
		$this->addField('facebook_page_url');
		$this->addField('instagram_page_url');
		$this->addField('rating')->type('Number');
		$this->addField('avg_cost');

		$this->addField('credit_card_accepted')->type('boolean');
		$this->addField('reservation_needed')->type('boolean');
		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('updated_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));

		$this->addField('longitude');
		$this->addField('latitude');
				
		$this->addField('is_featured')->type('boolean')->defaultValue(false);
		$this->addField('is_popular')->type('boolean')->defaultValue(false);
		$this->addField('is_recommend')->type('boolean')->defaultValue(false);
		
		//Restaurant Time Slot
		$this->addField('monday');
		$this->addField('tuesday');
		$this->addField('wednesday');
		$this->addField('thursday');
		$this->addField('friday');
		$this->addField('saturday');
		$this->addField('sunday');
		//slug URL
		$this->addField('url_slug');
		$this->addField('food_type')->setValueList(['veg'=>'Veg','nonveg'=>'Nonveg','mix'=>'Mix'])->mandatory(true);
		$this->addField('payment_method')->setValueList(['cash'=>'Cash','cheque'=>'Cheque','Paytm'=>'Paytm'])->mandatory(true);

		$this->addField('booking_policy')->type('text');
		$this->addField('cancellation_policy')->type('text');
		$this->addField('guidelines')->type('text');
		$this->addField('how_to_reach')->type('text');
		$this->addField('search_string')->type('text');

		$this->addField('status')->enum(["active","deactive"])->defaultValue('deactive');
		$this->addField('is_verified')->type('boolean')->defaultValue(false);

		// SEO Field
		$this->addField('title')->type('text')->hint('Ex: Best Venue in Udaipur - Destination/Venue Name');
		$this->addField('keyword')->type('text')->hint('Ex: best destination, destination in udaipur etc.');
		$this->addField('description')->type('text')->hint('Short description about your destination');
		$this->addField('image_title')->type('text')->hint('Ex:Destination in Udaipur - Destination Name');
		$this->addField('image_alt_text')->type('text')->hint('Ex:destination udaipur');


		$this->hasMany('Destination_HighlightAssociation','destination_id');
		$this->hasMany('Destination_Space','destination_id');
		$this->hasMany('Destination_Package','destination_id');
		$this->hasMany('Destination_VenueAssociation','destination_id');
		$this->hasMany('DestinationImage','destination_id');
		$this->hasMany('Review','destination_id');
		$this->hasMany('EventVenueRest','destination_id');
		
		$this->addExpression('first_category')->set(function($m,$q){
			return $m->refSQL('Destination_VenueAssociation')->setLimit(1)->fieldQuery('name');
		});


		$this->addExpression('package_count')->set(function($m,$q){
			return $m->refSQL('Destination_Package')->count();
		});

		$this->addHook('afterSave',[$this,'updateSearchString']);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function updateSearchString(){
		if(!$this->loaded())
			return;

		$search_string = ' ';
		$search_string .=" ".$this['name'];
		$search_string .=" ".$this['address'];
		$search_string .=" ".$this['city'];
		$search_string .=" ".$this['area'];
		$search_string .=" ".$this['state'];
		$search_string .=" ".$this['country'];
		$search_string .=" ".$this['id'];

		// // venue association
		$venues = $this->add('Model_Destination_VenueAssociation')->addCondition('destination_id',$this->id);
		foreach ($venues as $asso_venue) {
			$search_string .=" ". $asso_venue['name'];
		}
		$this['search_string'] = $search_string;
		$this->save();
	} 

	function getSpace(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination no record found");
			
		$space_model = $this->add('Model_Destination_Space')
                        ->addCondition('is_active',true)
                        ->addCondition('destination_id',$this->id)
                        ;
        // return $space_model->getRows();
        $output = [];
		foreach ($space_model as $space) {
			$output[] = ["id"=> $space['id'],'name'=>$space['name'],'cps'=>$space['cps'],'size'=>$space['size'],'type'=>$space['type'],'image'=>$space['icon_url']];
		}

		return $output;
	}

	function packageList(){
		if(!$this->loaded())
			throw new \Exception("model must loaded", 1);
		
		$model = $this->add('Model_Destination_Package')
                ->addCondition('is_active',true)
                ->addCondition('destination_id',$this->id)
                ;

		$array = [];
		foreach ($model as $package) {
			$v = $this->add('View')->setHtml('<h3 class="hungry-checkbox-label">'.$package['name']."<p> price = ".$package['price']."</p>"."<p>".$package['detail']."</p></h3>");
			$array[$package->id] = $v;
		}

		return $array;
	}

	function getPackage(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination no record found");

		$package_model = $this->add('Model_Destination_Package')
                        ->addCondition('is_active',true)
                        ->addCondition('destination_id',$this->id)
                        ;
     	$output = [];
		foreach ($package_model as $package) {
			$output[] = ['id'=>$package['id'],'name'=>$package['name'],'price'=>$package['price'],'detail'=>$package['detail']];
		}
		return $output;
		   
	}

	function getFacility(){
		$facility_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->id)
                        ->addCondition('highlight_type',"facility")
                        ->addCondition('is_active',true)
                        ;
        $output = [];
		foreach ($facility_model as $facility) {
			$output[] = ['id'=>$facility['id'],'name'=>$facility['destination_highlight'],'url'=>$facility['icon_url']];
		}
		return $output;
	}

	function occassionList(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);
		
		$occasion_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->id)
                        ->addCondition('highlight_type',"occasion")
                        ->addCondition('is_active',true)
                        ;
        $occasion_model->addExpression('name')->set($occasion_model->refSQL('destination_highlight_id')->fieldQuery('name'));
        
        $data = [];
        foreach ($occasion_model as $model){
        	$data[$model['destination_highlight_id']] = $model['name'];
        }
        return $data;
        return $occasion_model;
	}

	function getOccassion(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);
			
		$occasion_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->id)
                        ->addCondition('highlight_type',"occasion")
                        ->addCondition('is_active',true)
                        ;
     	$output = [];
		foreach ($occasion_model as $occasion) {
			$output[] = ['id'=>$occasion['id'],'name'=>$occasion['destination_highlight'],'url'=>$occasion['icon_url']];
		}
		return $output;   
	}

	function getService(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);
			
		$service_model = $this->add('Model_Destination_HighlightAssociation')
                        ->addCondition('destination_id',$this->id)
                        ->addCondition('highlight_type',"service")
                        ->addCondition('is_active',true)
                        ;
     	$output = [];
		foreach ($service_model as $service) {
			$output[] = ['id'=>$service['id'],'name'=>$service['destination_highlight'],'url'=>$service['icon_url']];
		}
		return $output;   
	}


	function getVenue(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);
        $venue_model = $this->add('Model_Destination_VenueAssociation')
                ->addCondition('destination_id',$this->id)
                ;
        $output = [];

        foreach ($venue_model as $venue) {
       	$output[] = ['id'=>$venue['id'],'name'=>$venue['name'],'url'=>$venue['icon_url']];
        }

        return $output;
	}

	function getGallary(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);

        $gallery_model = $this->add('Model_DestinationImage')
        				->addCondition('destination_id',$this->id)
        				->addCondition('is_active',true)
        				;
        $output = [];
        foreach ($gallery_model as $gallery) {
        	$output[] = ['name'=>$gallery['name'],'redirect_url'=>$gallery['redirect_urlredr'],'url'=>$gallery['image']];
        }

        return $output;
	}

	function getReview(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found", 1);

		$reviews = $this->add('Model_Review')
                    ->addCondition('destination_id',$this->id)
                    ->addCondition('is_approved',true)
                    ->getRows()
                    ;
        $temp_rew = [];
        foreach ($reviews as $rew) {
            $comment_model =$this->add('Model_Comment')->addCondition('review_id',$rew['id'])->addCondition('is_approved',true);
            $temp_rew[] = [
                    'id'=>$rew['id'],
                    "user_id"=>$rew['user_id'],
                    "user"=>$rew['user'],
                    "title"=>$rew['title'],
                    "review"=>$rew['comment'],
                    "created_at"=>$rew['created_at'],
                    "created_time"=>$rew['created_time'],
                    "comment"=>$comment_model->getRows(),
                    "rating"=>$rew['rating']
                    ];
            }            
        return $temp_rew;
	}

	function getEvent(){
		if(!$this->loaded())
			throw new \Exception("some thing wrong destination not found");

		    //get events
            $events_asso = $this->add('Model_EventDestinationRest')
                    ->addCondition('destination_id',$this->id)
                    ->getRows()
                    ;
            $temp_event = [];
            foreach ($events_asso as $temp) {
                $event_model = $this->add('Model_Event')
                                    ->addCondition('id',$temp['event_id'])
                                    ->addCondition('closing_date','>',$this->api->today)
                                    ->tryLoadany();
                
                if(!$event_model->loaded())
                    continue;
                    
                $temp_event[] = [
                				'id'=>$event_model['id'],
                                'name'=>$event_model['name'],
                                'detail'=>$event_model['detail'],
                                'starting_date'=>$event_model['starting_date'],
                                'starting_time'=>$event_model['starting_time'],
                                'closing_date'=>$event_model['closing_date'],
                                'closing_time'=>$event_model['closing_time'],
                                'display_image_id'=>$event_model['display_image_id'],
                                'lowest_price'=>$event_model['lowest_price'],
                                'total_day'=>$event_model['total_day'],
                                "disclaimer"=>$event_model['disclaimer'],
                                'city_id'=>$event_model['city_id'],
                                'city'=>$event_model['city'],
                                'state'=>$event_model['state'],
                                'state_id'=>$event_model['state_id'],
                                'country_id'=>$event_model['country_id'],
                                'country'=>$event_model['country']
                            ];
            }

			return $temp_event;

	}
}