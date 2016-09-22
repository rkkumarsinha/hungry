<?php

class View_Lister_Comment extends CompleteLister{
	public $restaurant_id;
	public $form;

	function init(){
		parent::init();

		$this->form = $form = $this->add('Form',null,'comment_form',['form/stacked']);
		$comment_model = $this->add('Model_Review')->addCondition('restaurant_id',$this->restaurant_id);
		$form->setModel($comment_model,['title','comment']);
		$form->addField('Number','rating')->validateNotNull()->addClass('hungryinputrating');

		if($data = $this->app->recall('reviewdata')){
			$r = $this->add('Model_Review');
			$r['restaurant_id'] = $data['restaurant_id'];
			$r['title'] = $data['title'];
			$r['comment'] = $data['comment'];
			$r['user_id'] = $this->app->auth->model->id;
			$r['rating'] = $data['rating'];
			$r->save();
			$this->js(true)->univ()->alert('Thank you for your valuable review');
			$this->app->forget('reviewdata');
		}

		$form->addSubmit('Submit Review')->addClass('atk-swatch-green')->addClass('hungrycommentsubmit');

		if($form->isSubmitted()){
			if(!is_numeric($form['rating']))
				$form->error('rating','0.5 to 5.o number are allowed');

			if($form['rating'] == 0)
				$form->error('rating','rating is a mandatory field');

			if($form['rating'] > 5 or $form['rating'] < 0)
				$form->error('rating','rating cannot be greater then 5');

			$title = $form['title'];
			$comment = $form['comment'];

			$data = ['title'=>$title,'comment'=>$comment,'restaurant_id'=>$this->restaurant_id,'rating'=>$form['rating']];
			$memorize_data = $this->app->recall('reviewdata');

			if(!is_array($memorize_data)){
				$this->app->memorize('reviewdata',$data);
			}

			if(!$this->app->auth->model->id){
				$form->js(null,$form->js()->reload())->_selector('#comment_modalpopup')->modal('show')->execute();
			}
			
			$comment_model['title'] = $title;
			$comment_model['comment'] = $comment;
			$comment_model['user_id'] = $this->app->auth->model->id;
			$comment_model['restaurant_id'] = $this->restaurant_id;
			$comment_model['rating'] = $form['rating'];
			$comment_model->save();
			$this->app->forget('reviewdata');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Thank you for your valuable review')->execute();
		}

	}

	function formatRow(){
		$this->current_row['profile_image'] = $this->model['profile_image_url']?:'assets/img/default-avatar.png';
		parent::formatRow();
	}

	function setModel($m){
		parent::setModel($m);
		$paginator = $this->add('Paginator');
        $paginator->setRowsPerPage(2);
	}
	
	function render(){
		$this->js()->_load('hungry');
		$this->js(true)->univ()->hungryInputRating();
		$this->js(true)->univ()->hungryRatingShow();
		parent::render();
	}

	function defaultTemplate(){
		return ['view/comment'];
	}
}