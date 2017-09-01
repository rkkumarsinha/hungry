<?php

class View_FilterMapSearch extends View{

	function init(){
		parent::init();
			
	}

	function render(){
		
		$search_data = $this->app->recall('search_data');
		if($search_data['city'])
			$city_id = $search_data['city'];
		else
			$city_id = $this->app->city_id;

		
		$rest_model = $this->add('Model_Restaurant');
		$rest_model->addCondition('status','active');
		$rest_model->addCondition('is_verified',true);
		
		if($search_data['keyword']){
			$rest_model->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.trim(implode(',', explode(" ",$search_data['keyword'])),",").'" IN NATURAL LANGUAGE MODE)');
	        $rest_model->addCondition(
	                        $rest_model->dsql()->orExpr()
	                        ->where($rest_model->getElement('Relevance'),'>',0)
	                        ->where($rest_model->getElement('search_string'), 'like', '%'.$search_data['keyword'].'%')
	                        );
		}
		$rest_model->addCondition('city_id',$city_id);

		// custom key word search with join

		$array_data = [];
		$count = 1;
		foreach ($rest_model as $rest) {
			// $avg_cost = $rest['avg_cost_per_person_veg'];
			$avg_cost = $rest->avgCost();

			$temp = [
					"id" => $rest->id,
					"category" => 'real_estate',
					"title" => $rest['name'],
					"location" => $rest['address']." ".$rest['area']." ".$rest['city']." ".$rest['state']." ".$rest['country'],
		            "latitude" => $rest['latitude'],
		            "longitude" => $rest['longitude'],
		            "url"=> $this->api->url('restaurant',['city'=>$rest['city'],'slug'=>$rest['url_slug']])->getURL(),
		            "type"=> $rest['category'],
		            "type_icon"=>$rest['category_icon_url'],
		            "rating"=> $rest['rating'],
		            "gallery"=>$rest->getImages(),
		            "features"=>["Frede Parking","Cards Accepted","Wi-Fi","Air Condition","Reservations","Teambuildings","Places to seat"],
		            "date_created"=> date('Y-m-d',strtotime($rest['created_at'])),
		            "price"=> $avg_cost,
		            "featured"=> $rest['is_featured']?1:$rest['is_popular']?1:$rest['is_recommend']?1:0,
		            "color"=> '',
		            "person_id" => 1,
		            "year" => 2016,
		            "special_offer" => 0,
		            "item_specific" => ["bathrooms"=> 0,"garages"=> ($rest['offers'] + $rest['discoouns'])],
            		"description" => $rest['about_restaurant'],
            		"last_review" => "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet",
            		"last_review_rating" => 5
				];

			$array_data[] = $temp;
			$count ++;
		}

		// note 
		            // "item_specific" => ["bedrooms"=> 2,"bathrooms"=> 2,"rooms"=> 4,"garages"=> 1,"area"=> 240],
		//  Bedroom = ; bathroom = ; rooms = ; garages = offer ; area = discount;
		$json_data = json_encode(['data'=>$array_data]);
		// exit;

		// echo $json_data;
		// exit;
		// $json_temp_data = '{
  //   "data": [
  //       {
  //           "id": 1,
  //           "category": "real_estate",
  //           "title": "Steak House Restaurant",
  //           "location": "63 Birch Street",
  //           "latitude": 51.541599,
  //           "longitude": -0.112588,
  //           "url": "item-detail.html",
  //           "type": "Apartment",
  //           "type_icon": "assets/icons/store/apparel/umbrella-2.png",
  //           "rating": 4,
  //           "gallery":
  //               [
  //                   "assets/img/items/1.jpg",
  //                   "assets/img/items/5.jpg",
  //                   "assets/img/items/4.jpg"
  //               ],
  //           "features":
  //               [
  //                   "Free Parking",
  //                   "Cards Accepted",
  //                   "Wi-Fi",
  //                   "Air Condition",
  //                   "Reservations",
  //                   "Teambuildings",
  //                   "Places to seat"
  //               ],
  //           "date_created": "2014-11-03",
  //           "price": "$2500",
  //           "featured": 0,
  //           "color": "",
  //           "person_id": 1,
  //           "year": 1980,
  //           "special_offer": 0,
  //           "item_specific":
  //               {
  //                   "bedrooms": 2,
  //                   "bathrooms": 2,
  //                   "rooms": 4,
  //                   "garages": 1,
  //                   "area": 240
  //               },
  //           "description": "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet pellentesque mauris. Proin sit amet scelerisque risus. Donec semper semper erat ut mollis. Curabitur suscipit, justo eu dignissim lacinia, ante sapien pharetra duin consectetur eros augue sed ex. Donec a odio rutrum, hendrerit sapien vitae, euismod arcu.",
  //           "last_review": "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet",
  //           "last_review_rating": 5
  //       }]}';
		// $this->js(true,'createHomepageGoogleMap(51.541216,-0.095678,'.$json_temp_data.');');
		$this->js(true,'createHomepageGoogleMap('.$this->app->city['latitude'].','.$this->app->city['longitude'].','.$json_data.');');
		parent::render();
	}

    function defaultTemplate(){
        return ['view/filtermapsearch'];
    }
}

