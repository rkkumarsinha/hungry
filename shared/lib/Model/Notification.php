<?php

class Model_Notification extends SQL_Model{
	public $table = "notification";

	function init(){
		parent::init();

		$this->hasOne('Country','country_id');
		$this->hasOne('State','state_id');
		$this->hasOne('City','city_id');

		$this->addField('name')->mandatory(true);
		$this->addField('created_at')->type('DateTime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('from_id');
		$this->addField('from')->setValueList(['Event'=>"Event",'Restaurant'=>"Restaurant",'Destination'=>"Destination","HungryDunia"=>"HungryDunia"]);
		$this->addField('message')->type('text');
		$this->addField('updated_at')->type('DateTime');
		$this->addField('to_id');
		$this->addField('to')->setValueList(['Event'=>"Event",'Restaurant'=>"Restaurant",'Destination'=>"Destination","HungryDunia"=>"HungryDunia"]);

		$this->addField('request_for')->setValueList(['discount'=>'Discount','offer'=>"Offer",'package'=>"Package",'image'=>"Image","pull push sticker"=>"Pull Push Sticker",'table reservation signature'=>"Table reservation signature","android app"=>"Android App",'website'=>"Website",'enquiry'=>"Enquiry"]);
		$this->addField('status')->setValueList(['approved'=>'Approved','pending'=>"Pending",'cancled'=>"Cancled"]);
		$this->addField('value');
		
		// $this->addExpression('from_name')->set(function($m,$q){
		// 	// IF([FROM]=Event,['Event_Name'],IF([FROM]=Destination,['Destination_Name'],'Not Found'))
		// 	return $q->expr("IF([FROM]=Restaurant,['Restaurant_Name'],'Not found')",
		// 					[
		// 						'FROM'=>$m->getElement('from'),
		// 						'Restaurant_Name'=>$m->add('Model_Restaurant')->tryLoad($m['from_id'])->getElement('name'),
		// 						// 'Event_Name'=>$m->add('Model_Event')->tryLoad($m->getElement('from_id'))->getElement('name'),
		// 						// 'Destination_Name'=>$m->add('Model_Destination')->tryLoad($m->getElement('from_id'))->getElement('name')
		// 					]

		// 				);
			
		// 	return '"no found"';
		// });

		$this->add('dynamic_model/Controller_AutoCreator');
	}

}