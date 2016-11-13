<?php

class Model_Restaurant extends SQL_Model{
	public $table = "restaurant";

	function init(){
		parent::init();

		$this->hasOne('User');
		$this->hasOne('Country','country_id')->mandatory(true);
		$this->hasOne('State','state_id')->mandatory(true);
		$this->hasOne('City','city_id')->mandatory(true);
		$this->hasOne('Area','area_id')->mandatory(true);
		$this->hasOne('Discount','discount_id')->mandatory(true);

		$this->add('filestore/Field_File','logo_image_id');
		$this->add('filestore/Field_File','banner_image_id'); //for detail
		$this->add('filestore/Field_File','display_image_id'); //for list

		$this->addField('name')->mandatory(true)->caption('Restaurant Name');
		$this->addField('owner_name')->mandatory(true);
		$this->addField('about_restaurant')->type('text')->mandatory(true)->display(array('form'=>'RichText'));
		$this->addField('disclaimer')->type('text')->mandatory(true)->display(array('form'=>'RichText'));
		$this->addField('address')->type('text');
		$this->addField('mobile_no')->hint('Comma separated multiple value');
		$this->addField('phone_no');
		$this->addField('email')->hint('Comma separated multiple value');
		$this->addField('website');
		$this->addField('facebook_page_url');
		$this->addField('instagram_page_url');
		$this->addField('rating')->type('Number');
		$this->addField('avg_cost_per_person_veg')->type('money');
		$this->addField('avg_cost_per_person_nonveg')->type('money');
		$this->addField('avg_cost_per_person_thali')->type('money');
		$this->addField('avg_cost_of_a_beer')->type('money');

		$this->addField('credit_card_accepted')->type('boolean');
		$this->addField('reservation_needed')->type('boolean');//table reservation
		$this->addField('type');
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
		
		// $this->addField('discount')->hint('Original Discount in Percentage');
		$this->addField('discount_subtract')->type('Number')->defaultValue(5);

		$this->addField('food_type')->setValueList(['veg'=>'Veg','nonveg'=>'Nonveg','mix'=>'Mix'])->mandatory(true);

		$this->addField('search_string')->type('text')->system(true)->defaultValue(null);

		$this->addField('status')->enum(["active","deactive"])->defaultValue('deactive');
		$this->addField('is_verified')->type('boolean')->defaultValue(false);

		// SEO Field
		$this->addField('title')->type('text')->hint('Ex: Best Restaurant in Udaipur - Restaurant Name');
		$this->addField('keyword')->type('text')->hint('Ex: best restaurant, restaurant in udaipur etc.');
		$this->addField('description')->type('text')->hint('Short description about your restaurant');
		$this->addField('image_title')->type('text')->hint('Ex:Restaurant in Udaipur - Restaurant Name');
		$this->addField('image_alt_text')->type('text')->hint('Ex:restaurant udaipur');

		$this->hasMany('CategoryAssociation','restaurant_id');
		$this->hasMany('Review','restaurant_id');
		$this->hasMany('RestaurantImage');
		$this->hasMany('RestaurantMenu');
		$this->hasMany('Restaurant_Highlight');
		$this->hasMany('Restaurant_Keyword');
		$this->hasMany('RestaurantOffer','restaurant_id');
		$this->hasMany('Comment','restaurant_id');
		$this->hasMany('EventDestinationRest','restaurant_id');
		$this->hasMany('ReservedTable','restaurant_id');
		$this->hasMany('Rating','restaurant_id');

		$this->addExpression('category')->set(function($m,$q){
			return $m->refSQL('CategoryAssociation')->setLimit(1)->fieldQuery('category');
		});

		$this->addExpression('offers')->set(function($m,$q){
			return $m->refSQL('RestaurantOffer')->count();
		});
		
		$this->addExpression('discounts')->set(function($m,$q){
			return $q->expr('IF([0]>0,1,0)',[$m->getElement('discount_id')]);
		});

		$this->addExpression('offer_discount_count')->set(function($m,$q){
			return $q->expr('[0]+[1]',[$m->getElement('offers'),$m->getElement('discounts')]);
		});

		$this->addExpression('category_icon_url')->set(function($m,$q){
			return $m->refSQL('CategoryAssociation')->setLimit(1)->fieldQuery('icon_url');
			// return $q->expr("replace([0],'/public','')",[$m->refSQL('category_id')->fieldQuery('image')]);
			// return $m->refSQL('Highlight_id')->fieldQuery('image');
		});

		$this->addExpression('approved_review_count')->set("'0'");

		$this->addExpression('discount_percentage')->set($this->refSQL('discount_id')->fieldQuery('name'));
		$this->addExpression('discount_percentage_to_be_given')->set(function($m,$q){
			return $q->expr('([0]-IFNULL([1],0))',[$m->getElement('discount_percentage'),$m->getElement('discount_subtract')]);
		});
		
		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('beforeSave',$this);
		$this->addHook('afterSave',$this);
		$this->addHook('afterLoad',$this);
		$this->addHook('beforeSave',[$this,'updateSearchString']);
	}

	function beforeSave(){
		$this['discount_subtract'] = $this['discount_subtract']?$this['discount_subtract']:5;
	}

	function afterLoad(){
		if(!$this->loaded()) return;

		$review = $this->add('Model_Review')
					->addCondition('restaurant_id',$this->id)
					->addCondition('is_approved',true)
					;
		$total_review = $review->count()->getOne();
		$total_rating = $review->sum('rating')->getOne();
		$this['rating'] = round(($this['rating'] + $total_rating) / ($total_review + 1),1);
		$this['approved_review_count'] = round($total_review,1);
	}

	function afterSave(){
		//check first if file exist or not
		// $filename = "../json/".strtoupper($this['city'])."/restaurant.json";
		// $rest = $this->add('Model_Restaurant')->addCondition('city_id',$this['city_id']);
		// file_put_contents($filename, json_encode($rest->getRows()));
		//if exist then empty the file
		//if exit not then create file 
		//write all restaurant json data
	}

	function updateSearchString(){
		
		if(!$this->loaded())
			return;

		$search_string = ' ';
		$search_string .=" ".$this['name'];
		$search_string .=" ".$this['food_type'];
		$search_string .=" ".$this['city'];
		$search_string .=" ".$this['area'];
		$search_string .=" ".$this['state'];
		$search_string .=" ".$this['country'];
		$search_string .=" ".$this['id'];

		// CategoryAssociation
		$categoryfields = $this->add('Model_CategoryAssociation')->addCondition('restaurant_id',$this->id);
		foreach ($categoryfields as $all_categoryfields) {
			$search_string .=" ". $all_categoryfields['category'];
		}

		//Restaurant_Highlight 
		$all_highlightfields = $this->add('Model_Restaurant_Highlight')->addCondition('restaurant_id',$this->id);
		foreach ($all_highlightfields as $highlightfields) {
			$search_string .=" ". $highlightfields['Highlight'];
		}

		//Restaurant_Keyword
		$all_keywords = $this->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$this->id);
		foreach ($all_keywords as $keywords) {
			$search_string .=" ". $keywords['keyword'];
		}
		
		$this['search_string'] = $search_string;
		$this->save();
	}

	function getOfferAndDiscount(){
		if(!$this->loaded())
			throw new \Exception("model must loaded", 1);
		
		$array = [];
		
		if($this['discount_id']){
			$array = ["d_".$this['discount_id']=>"Flat ".($this['discount_percentage'] - $this['discount_subtract'])." % "];
		}		

		$model = $this->add('Model_RestaurantOffer')->addCondition('restaurant_id',$this->id);
		foreach ($model as $offer) {
			$v = $this->add('View')->setHtml('<h3 class="hungry-checkbox-label">'.$offer['name']."<p>".$offer['detail']."</p></h3>");
			$array["o_".$offer->id] = $v;
			// $array["o_".$offer->id] = $offer['name'].$offer['detail'];
		}

		return $array;

	}

	function getImages(){
		if(!$this->loaded())
			throw new \Exception("Error Processing Request", 1);
								
		$rest_img = $this->add('Model_RestaurantImage')->addCondition('restaurant_id',$this->id);
		$ri_gallery = array();
		
		if(!$rest_img->count()->getOne()){
			$ri_gallery[0] = "assets/img/items/1.jpg";
			return $ri_gallery;
		}

		$count = 0;
		foreach ($rest_img as $ri){ 
			$ri_gallery[$count] = str_replace("public/", "",$rest_img['image']);
			$count++;
		}
		return $ri_gallery;
	}

	function avgCost(){
		if(!$this->loaded())
			return 0;

		$avg_cost = $this['avg_cost_per_person_veg']?:0;

		$cost_nonveg = $this['avg_cost_per_person_nonveg'];
		$cost_thali = $this['avg_cost_per_person_thali'];
		$cost_beer = $this['avg_cost_of_a_beer '];

		if($cost_nonveg > 0 && ($avg_cost - $cost_nonveg) > 0){
			$avg_cost = $cost_nonveg;
		}

		if($cost_thali > 0 && ($avg_cost - $cost_thali) > 0)
			$avg_cost = $cost_thali;

		if($cost_beer > 0 && ($avg_cost - $cost_beer) > 0)
			$avg_cost = $cost_beer;

		return $avg_cost;

	}

}