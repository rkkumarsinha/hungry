<?php

class Model_Voucher extends SQL_Model{
	public $table = "voucher";

	function init(){
		parent::init();

		$this->hasOne('Event','event_id')->mandatory(true);
		$this->hasOne('User','created_by_id')->defaultValue($this->app->auth->model->id)->mandatory(true);

		$this->addField('name')->mandatory(true);
		$this->addField('starting_date')->type('date')->defaultValue(date('Y-m-d'))->mandatory(true);
		$this->addField('expiry_date')->type('date')->defaultValue(date('Y-m-d'))->mandatory(true);

		$this->addField('voucher_based_on')->enum(['Price','Quantity'])->defaultValue('Quantity');
		$this->addField('voucher_applicable_min_value')->type('int')->mandatory(true);
		// $this->addField('voucher_applicable_max_value')->type('int')->mandatory(true);
		$this->addField('voucher_amount')->hint('20% or 100, you can define either in percentage(%) or amount');

		$this->addField('limit')->type('int')->hint('How many user ? i.e. 10 ');
		$this->addField('one_user_how_many_time')->type('int')->hint('one user how many time use same discount, leave empty for unlimitted times')->defaultValue(1);

		$this->addField('detail');
		$this->hasMany('VoucherUsed','voucher_id');

		$this->addExpression('total_used')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)', [$m->refSQL('VoucherUsed')->count()]);
		});

		// $this->addExpression('total_user_used')->set(function($m,$q){
		// 	$vu = $m->add('Model_VoucherUsed')->addCondition('voucher_id',$m->fieldQuery('id'));
		// 	$vu->_dsql()->group($vu->_dsql()->expr('[0]',[$vu->getElement('user_id')]));
		// 	return $q->expr('IFNULL([0],0)', [$vu->count()]);
		// });

		$this->addHook('beforeSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}	

	function beforeSave(){

		// $d1 = $this['starting_date'];
		// $d2 = $this['expiry_date'];

		// $d1 = (is_string($d1) ? strtotime($d1) : $d1);
		// $d2 = (is_string($d2) ? strtotime($d2) : $d2);

  //       $diff_secs = abs($d1 - $d2);
  //       $base_year = min(date("Y", $d1), date("Y", $d2));
  //       $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
  //       $days => date("j", $diff) - 1;
		// if($days < 0){

		$old_model = $this->add('Model_Voucher');
		$old_model->addCondition('name',$this['name']);
		$old_model->addCondition('event_id',$this['event_id']);
		$old_model->addCondition('id','<>',$this['id']);
		$old_model->tryLoadAny();
		if($old_model->loaded())
			throw $this->exception('voucher name is already added','ValidityCheck')
							->setField('name');

		if($this['starting_date'] > $this['expiry_date']){
			throw $this->exception('expiry date must be equal or grater then starting_date','ValidityCheck')->setField('expiry_date');
		}

		// if($this['voucher_applicable_min_value'] > $this['voucher_applicable_max_value']){
		// 	throw $this->exception('applicable value must be equal or greater then voucher min value','ValidityCheck')->setField('voucher_applicable_max_value');
		// }
	}

	function addVoucherUsed($event_ticket_id,$user_id){
		if(!$this->loaded())
			throw new \Exception("voucher model must loaded", 1);

		$voucher_used = $this->add('Model_VoucherUsed');
		$voucher_used['voucher_id'] = $this->id;
		$voucher_used['user_id'] = $user_id;
		$voucher_used['event_ticket_id'] = $event_ticket_id;
		$voucher_used['voucher_amount'] = 909;
		$voucher_used->save();
	}

	function applyCoupon($quantity,$price){
		$return = ['status'=>'failed','message'=>"not applicable",'discount_amount'=>0];

		if(!$this->loaded())
			return $return;

		if(!($this['expiry_date'] >= $this->api->today AND $this->api->today >=$this['starting_date']))
			return $return;


		if($this['voucher_based_on'] == "Price"){
			if($this['voucher_applicable_min_value'] > $price)
				return $return;
		}

		if($this['voucher_based_on'] == "Quantity"){
			if($this['voucher_applicable_min_value'] > $quantity)
				return $return;
		}

		if($this['total_used'] >= $this['limit']){
			return $return;
		}

		if($this['one_user_how_many_time']){
			$used_model = $this->add('Model_VoucherUsed')
							->addCondition('voucher_id',$this->id)
							->addCondition('user_id',$this->app->auth->id)
							;
			if($used_model->count()->getOne() >= $this['one_user_how_many_time'])
				return $return;
		}

		$temp = explode("%", $this['voucher_amount']);

		$amount = $price * $quantity;
		if(isset($temp[1])){
			$discount = round($temp[0] * $amount / 100);
		}else
			$discount = $temp[0];

		return ['status'=>'success','discount_amount'=>$discount];
	}
}