<?php

class View_Lister_Thumbnail extends CompleteLister{
	

	function setModel($model){
		parent::setModel($model);

	}

	function formatRow(){		
		$f = $this->add('filestore/Model_File')->load($this->model['image_id']);
		$path = "http://localhost/hungrydunia/".str_replace("..", "", $f->getPath());
		$this->current_row['thumbnail_img'] = $path;
		// throw new \Exception($path);
		
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/thumbnail'];
	}
}