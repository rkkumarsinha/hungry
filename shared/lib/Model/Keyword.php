<?php

class Model_Keyword extends SQL_Model{
	public $table = "keyword";
	function init(){
		parent::init();

		$this->addField('name')->caption('keyword')->mandatory(true);
		$this->add('filestore/Field_Image','image_id')->mandatory(true);

		$this->addHook('afterSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}	

	function afterSave(){
		$this->updateKeywordJson();
	}


	function updateKeywordJson(){
		$keywords = $this->add('Model_Keyword');
		$dir_path = '../json/keyword.json';
		if(!file_exists($dir_path))
			mkdir($dir_path, 0755, true);

		// $datas = $keywords->getRows();
		// echo "<pre>";
		$data = [];
		foreach ($keywords as $key) {
			$temp = [];
			$temp['id'] = $key->id;
			$temp['name'] = $key['name'];
			// $temp['restaurant'] = $this->getRestaurant($key->id);
			$data[] = $temp;
		}

		// print_r($data);
		file_put_contents($dir_path, json_encode($data));
	}

	function getRestaurant($key_word_id){
		$key_rest = $this->add('Model_Restaurant_Keyword')->addCondition('keyword_id',$key_word_id);
		return $key_rest->getRows(['restaurant_id','restaurant']);
	}
}