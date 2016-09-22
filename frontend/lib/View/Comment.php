<?php

class View_Comment extends CompleteLister{
	public $review_id;
	function init(){
		parent::init();

		$review_id =  $this->review_id;
		if(!$this->api->auth->model->loaded()){
			$this->add('View_Warning',null,'not_found')->set('unauthorize');
			return;
		}

		if(!$review_id){
			$this->add('View_Warning',null,'not_found')->set('No Review Selected '.$review_id);
			$this->template->trySet('comment_wrapper',"");
		}

		$comment_lister_model = $this->add('Model_Comment')
						->addCondition('review_id',$review_id)
						->addCondition('user_id',$this->app->auth->model->id)
						->setOrder('created_at','desc');
		
		if($review_id and !$comment_lister_model->count()->getOne()){
			$this->add('View_Warning',null,'not_found')->set('No Record Found');
		}

		$this->setModel($comment_lister_model);

		if($review_id){
			$comment_form = $this->add('Form',null,'comment_form',['form/stacked']);
			$comment_form->setStyle('width','90%');
			$comment_form->addClass('pull-right');

			$comment_form->addField('text','comment');
			$comment_form->addSubmit("Post");

			if($comment_form->isSubmitted()){
				$comment_model = $this->add('Model_Comment');
				$comment_model['review_id'] = $review_id;
				$comment_model['user_id'] = $this->app->auth->model->id;
				$comment_model['is_approved'] = 0;
				$comment_model['comment'] = $comment_form['comment'];
				$comment_model->save();

				$comment_form->js(null,$this->js()->reload())->univ()->successMessage('Comment post successfully')->execute();
			}
		}

	}

	function defaultTemplate(){
		return ['view/hostaccount/comment'];
	}
}