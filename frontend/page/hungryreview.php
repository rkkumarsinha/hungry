<?php

class page_hungryreview extends Page{

	function page_index(){
		

		//check for the numbner is between 
		$rating = $_POST['hungry_rating'];

		$restaurant_id = $_POST['hungry_restaurant'];
		
		$comment_id = $_POST['hungry_comment']?:0;

		//1st check for login
		// if(!$this->api->auth->isLoggedIn()){
		// 	$options = [
		// 			'width'=>600
		// 		];
		// 	$this->js(null)->univ()->frameURL('Login Panel',$this->api->url('login'),$options)->execute();
		// 	echo "hungry login false:101";
		// 	exit;
		// }

		//2nd check for current login user already given a rating or not


		$review = $this->add('Model_Review');
		$review['restaurant_id'] = $restaurant_id;
		$review['comment_id'] = $comment_id;
		$review['rating'] = $rating;
		if($review->save()){
			echo "true";
			exit;
		}

		echo "false";
		exit;

	}
}