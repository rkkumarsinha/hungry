<?php

class page_importer extends Page{
	
	public $rest_str;
	public $rest_array = [];
	public $rest_image = [];
	public $rest_menu = [];
	public $rest_highlight = [];
	public $rest_keyword = [];
	public $rest_review = [];

	function init(){
		parent::init();

		// if(file_exists('/var/www/html/hungrydunia/admin/images/01.jpg'))
		// 	echo "Checking File exist ";
		// else
		// 	echo "Checking File Not exist ";

		// exit;
		// $ostate = $this->add('Model_Oldstate');
		// foreach ($ostate as $os) {
		// 	$s = $this->add('Model_State');
		// 	$s['name'] = $os['name'];
		// 	$s['country_id'] = $os['country_id'];
		// 	$s->save();
		// }

		// $oc = $this->add('Model_Oldcity');
		// foreach ($oc as $o) {
		// 	$c = $this->add('Model_City');
		// 	$c['name'] = $o['city'];
		// 	$c['state_id'] = $o['state_id'];
		// 	$c->save();
		// }

		// $oa = $this->add('Model_OldArea');
		// foreach ($oa as $o) {
		// 	$area = $this->add('Model_Area');
		// 	$area['name'] = $o['name'];
		// 	$area['city_id'] = $o['city_id'];
		// 	$area->save();
		// }

		// $ok = $this->add('Model_Oldkeyword');
		// foreach ($ok as $o) {
		// 	$k = $this->add('Model_Keyword');
		// 	$k['name'] = $o['keyword'];

		// 	$true = 1;
		// 	if($o['deleted'] == 1)
		// 		$true = 0;

		// 	$k['is_active'] = $true;
		// 	$k->save();
		// }

		// $ok = $this->add('Model_Oldhighlight');
		// foreach ($ok as $o) {
		// 	$k = $this->add('Model_Highlight');
		// 	$k['name'] = $o['type'];

		// 	$true = 1;
		// 	if($o['deleted'] == 1)
		// 		$true = 0;

		// 	$k['is_active'] = $true;
		// 	$k->save();
		// }


		// $olds = $this->add('Model_OldRest');
		// foreach ($olds as $old) {
			
		// 	$new = $this->add('Model_Restaurant');
		// 	$new['country_id'] = 1;
		// 	$new['user_id'] = 1;
		// 	$new['state_id'] = $old['state'];
		// 	$new['city_id'] = $old['city'];
		// 	$new['area_id'] = $old['area'];

		// 	$new['name'] = $old['name'];
		// 	$new['owner_name'] = $old['name'];
		// 	$new['about_restaurat'] = $old['description'];
		// 	$new['address'] = $old['address'];

		// 	$new['mobile_no'] = $old['contact_no'];
		// 	if($old['contact_no_2'])
		// 	$new['mobile_no'] = $old['contact_no'].",".$old['contact_no_2'];
				
		// 	$new['phone_no'] = $old['phoneno'];
		// 	$new['email'] = $old['email'];
		// 	$new['website'] = $old['website'];
		// 	$new['facebook_page_url'] = $old['facebook_url'];
		// 	$new['instagram_page_url'] = "";
		// 	$new['rating'] = $old['rating'];
		// 	$new['avg_cost_per_person'] = $old['cost_per_person'];
		// 	$new['url_slug'] = $old['slug'];
		// 	$new['longitude'] = $old['longitude'];
		// 	$new['latitude'] = $old['latitude'];
		// 	$new['credit_card_accepted'] = $old['credit_card'];
		// 	$new['reservation_needed'] = $old['reservation'];
		// 	$new['created_at'] = $old['created_on'];
		// 	$new['updated_at'] = $old['modified_on'];
		// 	$new['is_active'] = ($old['deleted']?0:1);

		// 	$new['is_featured'] = 1;
		// 	$new->save();

		// 	// $file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'move','import_source'=>));
		// 	// $file['filestore_volume_id'] = $file->getAvailableVolumeID();
		// 	// $file['original_filename'] = $attach->name;
		// 	// $file->save();
		// 	// $mail_m->addAttachment($file->id,$attach->name);

		// 	$new->unload();
		// }


		//upload data 
		
		$dsql = $this->app->db->dsql();
		$restaurants = $dsql->table('bf_restaurant');
		foreach ($restaurants as $rest){
			$this->rest_array[$rest['id']] = array();
			$this->rest_array[$rest['id']]['name'] = $rest['name'];
			$this->rest_array[$rest['id']]['slug'] = $rest['slug'];
			$this->rest_array[$rest['id']]['default_image'] = $rest['default_image']; // display image
			$this->rest_array[$rest['id']]['banner'] = $rest['banner']; // banner
			$this->rest_array[$rest['id']]['logo'] = $rest['logo']; //logo

		}

		// //creating  image array
		// $dsql = $this->app->db->dsql();
		// $images = $dsql->table('bf_restaurant_images');

		// foreach ($images as $image) {
		// 	if(!isset($this->rest_image[$image['restaurant_id']]))
		// 		$this->rest_image[$image['restaurant_id']]['gallery'] = [];
			
		// 	$temp = $image['image'];

		// 	array_push($this->rest_image[$image['restaurant_id']]['gallery'],$temp);
		// }
		
		// //creating image menu array
		// $dsql = $this->app->db->dsql();
		// $menus = $dsql->table('bf_restaurant_menu');

		// foreach ($menus as $menu) {
		// 	if(!isset($this->rest_menu[$menu['restaurant_id']]))
		// 		$this->rest_menu[$menu['restaurant_id']]['menu'] = [];
			
		// 	$temp = $menu['menu'];

		// 	array_push($this->rest_menu[$menu['restaurant_id']]['menu'],$temp);

		// }

		
		// $dsql = $this->app->db->dsql();
		// $highlights = $dsql->table('bf_restaurant_highlights');

		// foreach ($highlights as $highlight) {
		// 	if(!isset($this->rest_highlight[$highlight['restaurant_id']]))
		// 		$this->rest_highlight[$highlight['restaurant_id']]['highlights'] = [];
			
		// 	$temp = $highlight['highlights_id'];

		// 	array_push($this->rest_highlight[$highlight['restaurant_id']]['highlights'],$temp);

		// }

		// $dsql = $this->app->db->dsql();
		// $keywords = $dsql->table('bf_restaurant_tags');

		// foreach ($keywords as $keyword) {
		// 	if(!isset($this->rest_keyword[$keyword['restaurant_id']]))
		// 		$this->rest_keyword[$keyword['restaurant_id']]['keywords'] = [];
			
		// 	$temp = $keyword['tag'];

		// 	array_push($this->rest_keyword[$keyword['restaurant_id']]['keywords'],$temp);

		// }

		$dsql = $this->app->db->dsql();
		$reviews = $dsql->table('bf_restaurant_reviews');

		foreach ($reviews as $review) {
			if(!isset($this->rest_review[$review['restaurant_id']]))
				$this->rest_review[$review['restaurant_id']]['comments'] = [];

			$dsql = $this->app->db->dsql();
			$old_user = $dsql->table('bf_users')->where('id',$review['id']);

			foreach ($old_user as $ou) {
				$temp = [
						"title"=>$review['title'],
						'comment'=>$review['comment'],
						'created_at'=>$review['date'],
						'created_time'=>$review['time'],
						'is_approved'=>$review['approved']?:0,
						'user_id'=>$ou['email']
					];
			}

			array_push($this->rest_review[$review['restaurant_id']]['comments'],$temp);

		}

		// echo "<pre>";
		// print_r($this->rest_review);
		// exit;
		//for each for restaurant array
			//load restaurant according to name or slug

			//for each of rest images array
				//load model ref model of images(restaurant)
				//load filestore model and save images 
				//save file_image id into images model

		//

		// $this->updateUser();
		foreach ($this->rest_array as $rest_id => $rest) {

			$rest_model = $this->add('Model_Restaurant')->tryloadBy('name',$rest['name']);
			if(!$rest_model->loaded())
				continue;
			//update restaurant logo/banner/display

			$this->updateReview($rest_id,$new_rest_id=$rest_model->id);
			// if($rest['default_image']){
			// 	$image_name = $rest['default_image'];
			// 	$image_path = '/var/www/html/hungrydunia/admin/images/'.$image_name;

			// 	$exist = $this->checkImageExist($image_path);

			// 	if($exist){
			// 		$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$image_path));
			// 		$file['filestore_volume_id'] = $file->getAvailableVolumeID();
			// 		$file['original_filename'] = $image_name;
			// 		$file->save();

			// 		$rest_model['display_image_id'] = $file->id;
			// 		$rest_model->save();
			// 	}

			// }

			// if($rest['banner']){
				
			// 	$image_name = $rest['banner'];
			// 	$image_path = '/var/www/html/hungrydunia/admin/banner/'.$image_name;

			// 	$exist = $this->checkImageExist($image_path);
			// 	// echo "banner = ".$rest['banner'];

			// 	if($exist){
			// 		$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$image_path));
			// 		$file['filestore_volume_id'] = $file->getAvailableVolumeID();
			// 		$file['original_filename'] = $image_name;
			// 		$file->save();

			// 		$rest_model['banner_image_id'] = $file->id;
			// 		$rest_model->save();
			// 	}
			// }

			// if($rest['logo']){

			// 	$image_name = $rest['logo'];
			// 	$image_path = $this->checkImageExist($image_name);

			// 	if($image_path){
			// 		$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$image_path));
			// 		$file['filestore_volume_id'] = $file->getAvailableVolumeID();
			// 		$file['original_filename'] = $image_name;
			// 		$file->save();

			// 		$rest_model['logo_image_id'] = $file->id;
			// 		$rest_model->save();
			// 	}
			// }

			//import menu images
			// $this->importMenu($rest_id,$new_rest_id=$rest_model->id);

			// echo $rest_model['name']." id =".$rest_id." New Rest ID = $rest_model->id name= ".$rest_model['name']."<br/>";
			// continue;

			// $this->importHighlight($rest_id,$new_rest_id=$rest_model->id);
			// $this->importKeyword($rest_id,$new_rest_id=$rest_model->id);

		}
	}

	function updateReview($old_rest_id,$new_rest_id){
			
		$rest_review_array = $this->rest_review[$old_rest_id];

		$rest_review_array = $rest_review_array['comments'];
		if(!is_array($rest_review_array))
			return;

		foreach ($rest_review_array as $id => $comment_array) {

			$new_user = $this->add('Model_User')->tryLoadBy('email',$comment_array['user_id']);

			$comment = $this->add('Model_Comment');
			$comment['restaurant_id'] = $new_rest_id;
			$comment['user_id'] = $new_user->id;
			$comment['title'] = $comment_array['title'];
			$comment['comment'] = $comment_array['comment'];
			$comment['created_at'] = $comment_array['created_at'];
			$comment['created_time'] = $comment_array['created_time'];
			$comment['is_approved'] = $comment_array['is_approved'];
			$comment->saveAndUnload();
		}
	}

	function updateUser(){
		$dsql = $this->app->db->dsql();
		$users = $dsql->table('bf_users');

		foreach ($users as $u) {
			$user = $this->add('Model_User');
			$user['name'] = $u['username'];
			$user['password'] = '';
			$user['email'] = $u['email'];
			$user['created_at'] = $u['created_on'];
			$user['status'] = 'active';
			$user['is_verified'] = 1;
			$user['password_hash'] = $u['password_hash'];
			$user['updated_at'] = $u['last_login'];
			$user['is_active'] = 1;
			$user['verification_code'] = "";
			$user['type'] = "user";
			// $user['dob'] = ;
			// $user['mobile'] = ;
			$user['received_newsletter'] = 1;
			$user->saveAndUnload();
		}

	}


	function importKeyword($old_rest_id,$new_rest_id){

		$rest_keyword_array = $this->rest_keyword[$old_rest_id];

		$rest_keyword_array = $rest_keyword_array['keywords'];
		if(!is_array($rest_keyword_array))
			return;

		foreach ($rest_keyword_array as $id => $keyword_id) {

			$rest_key = $this->add('Model_Restaurant_Keyword');
			$rest_key['restaurant_id'] = $new_rest_id;
			$rest_key['keyword_id'] = $keyword_id;
			$rest_key->saveAndUnload();
		}

	}


	function importHighlight($old_rest_id,$new_rest_id){
		$rest_highlight_array = $this->rest_highlight[$old_rest_id];

		$rest_highlight_array = $rest_highlight_array['highlights'];
		if(!is_array($rest_highlight_array))
			return;

		foreach ($rest_highlight_array as $id => $highlight_id) {

			$rest_high = $this->add('Model_Restaurant_Highlight');
			$rest_high['restaurant_id'] = $new_rest_id;
			$rest_high['Highlight_id'] = $highlight_id;
			$rest_high->saveAndUnload();
		}
	}

	function checkImageExist($image_path){
			if(file_exists($image_path))
				return true;
			else{
				return false;
			}
	}

	function importMenu($old_rest_id,$new_rest_id){

		$rest_menu_array = $this->rest_menu[$old_rest_id];

		$rest_menu_array = $rest_menu_array['menu'];
		if(!is_array($rest_menu_array))
			return;

		foreach ($rest_menu_array as $id => $image_name) {
			$image_path = '/var/www/html/hungrydunia/admin/menu/'.$image_name;
			if(file_exists($image_path))
				echo "file_exists"."<br/>";
			else{
				echo "File not exist"."<br/>";
				continue;
			}

			$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$image_path));
			$file['filestore_volume_id'] = $file->getAvailableVolumeID();
			$file['original_filename'] = $image_name;
			$file->save();

			$rest_image = $this->add('Model_RestaurantMenu');
			$rest_image['restaurant_id'] = $new_rest_id;
			$rest_image['image_id'] = $file->id;
			$rest_image->saveAndUnload();

		}


	}

	function importImage(){
		//restaurant image importer is working
		$rest_image_array = $this->rest_image[$rest_id];
		$rest_gallery_array = $rest_image_array['gallery'];
		if(!is_array($rest_image_array))
			continue;

		foreach ($rest_gallery_array as $id => $image_name) {
			$image_path = '/var/www/html/hungrydunia/admin/images/'.$image_name;
			if(file_exists($image_path))
				echo "file_exists"."<br/>";
			else{
				echo "File not exist"."<br/>";
				continue;
			}

			$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$image_path));
			$file['filestore_volume_id'] = $file->getAvailableVolumeID();
			$file['original_filename'] = $image_name;
			$file->save();

			$rest_image = $this->add('Model_RestaurantImage');
			$rest_image['restaurant_id'] = $rest_model->id;
			$rest_image['image_id'] = $file->id;
			$rest_image->saveAndUnload();

		}
	}

}