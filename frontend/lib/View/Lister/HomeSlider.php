<?php

class View_Lister_HomeSlider extends CompleteLister{
	public $city;
	public $count = 0;
	public $type;
	function init(){
		parent::init();
				
		$city = $this->add('Model_City')->addCondition('name',$this->city)->tryLoadAny();
		$image = $city->ref('Image')->addCondition('is_active',true);
		if($this->type){
			$image->addCondition('type',$this->type);
		}
		$this->setModel($image);
	}

	function setModel($m){
		parent::setModel($m);
	}
	
	function formatRow(){
		$this->count++;
		if($this->count===1)
			$this->current_row['firstslide'] = "active";
		else
			$this->current_row['firstslide'] = "inactive";

		$str = "";
		if($this->model['redirect_url'])
			$str .= "<a href='".$this->model['redirect_url']."'>";

        $img_url = str_replace("/public", "", $this->model['image']);
        $str .= "<img src='".$img_url."'/>";

		if($this->model['redirect_url'])
			$str .="</a>";

		$this->current_row_html['image'] = $str;

		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/homeslider'];
	}
}