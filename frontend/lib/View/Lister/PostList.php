<?php

class View_Lister_PostList extends CompleteLister{
		
	function setModel($model){
		parent::setModel($model);

	}

	function formatRow(){
		parent::formatRow();
		$temp = explode("/upload", $this->model['post_image']);
		$url = $this->app->getConfig('imagepath')."/upload".$temp[1];
		$this->current_row['post_image'] = $url;

		$this->current_row['detail_url'] = $this->app->url('blog-detail',['post'=>$this->model['slug']]);
	}

	function defaultTemplate(){
		return ['view/postlist'];
	}
}