<?php

class page_quickview extends Page{
	function init(){
		parent::init();

		if(!$_POST['restaurantid']){
			echo "no record found";
			exit;
		}

		
		$rest = $this->add('Model_Restaurant')->tryLoad($_POST['restaurantid']);
		if(!$rest->loaded()){
			echo "no record found";
			exit;	
		}

		$quickview = $this->add('View',null,null,['view/quickview']);
		$quickview->setModel($rest);

		$quickview->template->trySet('avg_cost',$rest->avgCost());
		$quickview->template->trySet('detail_url',$this->api->url('restaurant',['slug'=>$rest['url_slug']])->getURL());
		
		$image_lister = $quickview->add('Lister',null,'gallery_images',['view/quickview','gallery_images']);
		$image_lister->setSource($rest->getImages());
			
		$feature_lister = $quickview->add('Lister',null,'features',['view/quickview','features']);
		$feature_lister->setModel($this->add('Model_Restaurant_Highlight')->addCondition('is_active',true)->addCondition('restaurant_id',$rest->id));

		$cuisine_lister = $quickview->add('Lister',null,'keyword',['view/quickview','keyword']);
		$cuisine_lister->setModel($this->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$rest->id));

		$comment = $this->add('Model_Review')
                        ->addCondition('restaurant_id',$rest->id)
                        ->addCondition('is_approved',true)
                        ->setOrder('created_at','desc')
                        ->setLimit(1)->tryLoadAny();
        
        // throw new \Exception((($comment['rating'] * 100)/5.0));
        
        if($comment->loaded()){
        	$per = (($comment['rating'] * 100)/5.0);
        	if(!$per)
        		$per = 0.1;

        	$per = "width:".$per."%";
	        $quickview->template->trySet('last_review_rating',$comment['rating']);
	        $quickview->template->trySet('last_review_rating_percentage',$per);
	        $quickview->template->trySet('last_review_title',$comment['title']);
	        $quickview->template->trySet('last_review',$comment['comment']);
        }else{
	        $quickview->template->trySet('last_review',"no comment found");
	        $quickview->template->tryDel('review_rating_wrapper');
        }

        
		echo $quickview->getHtml();
		exit;

		// $temp = [
		// 		"id" => $rest->id,
		// 		"category" => 'real_estate',
		// 		"title" => $rest['name'],
		// 		"location" => $rest['address']." ".$rest['area']." ".$rest['city']." ".$rest['state']." ".$rest['country'],
	 //            "latitude" => $rest['latitude'],
	 //            "longitude" => $rest['longitude'],
	 //            "type"=> $rest['category'],
	 //            "type_icon"=>$rest['category_icon_url'],
	 //            "rating"=> $rest['rating'],
	 //            "gallery"=>$rest->getImages(),
	 //            "features"=>["Frede Parking","Cards Accepted","Wi-Fi","Air Condition","Reservations","Teambuildings","Places to seat"],
	 //            "date_created"=> date('Y-m-d',strtotime($rest['created_at'])),
	 //            "price"=> $avg_cost,
	 //            "featured"=> $rest['is_featured']?1:$rest['is_popular']?1:$rest['is_recommend']?1:0,
	 //            "color"=> '',
	 //            "person_id" => 1,
	 //            "year" => 1980,
	 //            "special_offer" => 0,
	 //            "item_specific" => ["bedrooms"=> 2,"bathrooms"=> 2,"rooms"=> 4,"garages"=> 1,"area"=> 240],
  //       		"description" => $rest['about_restaurant'],
  //       		"last_review" => "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet",
  //       		"last_review_rating" => 5
		// 	];

	}
}