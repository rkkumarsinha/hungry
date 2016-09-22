<?php

class Model_Notification extends SQL_Model{
	public $table = "notification";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('created_at')->type('DateTime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('from_id');
		$this->addField('from')->setValueList(['Event'=>"Event",'Restaurant'=>"Restaurant",'Destination'=>"Destination","HungryDunia"=>"HungryDunia"]);
		$this->addField('message')->type('text');
		$this->addField('updated_at')->type('DateTime');
		$this->addField('to_id');
		$this->addField('to')->setValueList(['Event'=>"Event",'Restaurant'=>"Restaurant",'Destination'=>"Destination","HungryDunia"=>"HungryDunia"]);

		$this->addField('request_for')->setValueList(['discount'=>'Discount','offer'=>"Offer",'package'=>"Package",'image'=>"Image","pull push sticker"=>"Pull Push Sticker",'table reservation signature'=>"Table reservation signature","android app"=>"Android App",'website'=>"Website"]);
		$this->addField('status')->setValueList(['approved'=>'Approved','pending'=>"Pending",'cancled'=>"Cancled"]);
		$this->addField('value'); 

		$this->add('dynamic_model/Controller_AutoCreator');
	}

}