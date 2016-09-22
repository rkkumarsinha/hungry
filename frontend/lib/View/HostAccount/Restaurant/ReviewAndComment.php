<?php

class View_HostAccount_Restaurant_ReviewAndComment extends View{
	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");
		
		$host_restaurant = $this->app->listmodel;

		$review_model = $this->add('Model_Review')->addCondition('is_approved',true)->addCondition('restaurant_id',$host_restaurant->id)->setOrder('created_at','desc')->setOrder('is_approved','asc');

		$review_lister = $this->add("CompleteLister",null,'review_lister',['view/hostaccount/reviewcomment','review_lister']);
		$review_lister->setModel($review_model);
		

		$review_id = $this->api->stickyGET('reviewid');
		$comment_view = $this->add('View_Comment',['review_id'=>$_GET['reviewid']],'comment_lister');

		$comment_url = $this->api->url(null,['cut_object'=>$comment_view->name]);
		// $this_url = $this->api->url(null,['cut_object'=>$right->name]);
		$review_lister->on('click','.host-comment',function($js,$data)use($comment_url,$comment_view){
			$js = [
					$this->js(true)->_selector('li.comment')->removeClass('review-active'),
					$this->js(true)->_selector('li.comment[data-reviewid="'.$data['reviewid'].'"]')->addClass('review-active'),
					$comment_view->js()->reload(['reviewid'=>$data['reviewid']],null,$comment_url)
                ];
            return $js;
		});
	}

	function defaultTemplate(){
		return ['view/hostaccount/reviewcomment'];
	}
}
		// $grid->add('VirtualPage')
		// 		->addColumn('comments')
		// 		->set(function($page)use($grid){
		// 			$id = $_GET[$page->short_name.'_id'];
		// 			$comment_model = $page->add('Model_Comment')
		// 							->addCondition('review_id',$id)
		// 							->addCondition('user_id',$this->app->auth->model->id)
		// 							->addCondition('is_approved',true)
		// 							;
		// 			$page->add('CRUD')->setModel($comment_model,['comment']);
		// 	});