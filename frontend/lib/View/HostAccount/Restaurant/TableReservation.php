<?php

class View_HostAccount_Restaurant_TableReservation extends CompleteLister{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$this->addClass('hungry-reserve-table-host');


		$host_restaurant = $this->app->listmodel;
		$this->api->stickyGET('actionon');
		$this->api->stickyGET('actiontype');
		$grid = $this;

		$vp = $this->add('VirtualPage')->set(function($p)use($grid){
				if(!$_GET['actionon']){
					$p->add('View_Error')->set("action on not exist");
					return;
				}

				$id = $_GET['actionon'];

				if($_GET['actiontype'] === "Verify"){
					// $p->add('View_Info')->set('Todo Table Booking Information'.$_GET['actiontype']." = ".$_GET['actionon']);
					$form = $p->add('Form',null,null,['form/empty']);
					$col = $form->add('Columns');
					$col_total_amount = $col->addColumn(2);
					$col_total_amount->add('View')->setElement('lable')->set('Total Amount');
					$col_discount_percentage = $col->addColumn(2);
					$col_discount_percentage->add('View')->setElement('lable')->set('Discount Percentage');
					$col_discount_amount = $col->addColumn(2);
					$col_discount_amount->add('View')->setElement('lable')->set('Discount Amount');
					$col_amount_to_be_paid = $col->addColumn(3);
					$col_amount_to_be_paid->add('View')->setElement('lable')->set('Amount To be Paid');
					$col_payment_mode = $col->addColumn(3);
					$col_payment_mode->add('View')->setElement('lable')->set('Payment Mode');

					$total_amount_field = $col_total_amount->addField('Number','total_amount')->validateNotNull();
					$d_percentage_field = $col_discount_percentage->addField('Number','discount_percentage')->validateNotNull();
					$discount_amount_field = $col_discount_amount->addField('Number','discount_amount');
					$col_amount_to_be_paid->addField('Number','amount_to_be_paid')->validateNotNull();
					$col_payment_mode->addField('DropDown','payment_mode')->setValueList(['cash'=>"Cash",'card'=>"Card",'e-wallet'=>"E-wallet"])->validateNotNull()->setEmptyText('Please Select Payment Mode');
					$form->addSubmit('Verify');

					$this->api->stickyGET('total_amount');
					if($_GET['total_amount']){
						$discount = $_GET['discount_percentage'];
						if(!is_numeric($discount))
							$discount = 0;
						$discount_amount_field->set(($_GET['total_amount'] * $discount / 100.00))->setAttr('Disabled',true);
					}

					$total_amount_field->js('change',$discount_amount_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$discount_amount_field->name]),'total_amount'=>$total_amount_field->js()->val(),'discount_percentage'=>$d_percentage_field->js()->val()]));
					$d_percentage_field->js('change',$discount_amount_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$discount_amount_field->name]),'total_amount'=>$total_amount_field->js()->val(),'discount_percentage'=>$d_percentage_field->js()->val()]));

					if($form->isSubmitted()){						
						$reserved_table_model = $p->add('Model_ReservedTable')->load($id);
						$discount_taken = $form['total_amount'] * $form['discount_percentage'] / 100.00;
						$reserved_table_model['discount_taken'] = $discount_taken;
						$reserved_table_model['status'] = 'verified';
						$reserved_table_model['total_amount'] = $form['total_amount'];
						$reserved_table_model['amount_paid'] = $form['total_amount'] - $discount_taken;
						$reserved_table_model['payment_mode'] = $form['payment_mode'];
						$reserved_table_model->save();
						
						
						// Todo send email or sms
						$js = [
							$form->js()->closest('.dialog')->dialog('close'),
							$grid->js()->_selector('div[data-recordid='.$_GET['actionon'].']')->hide()
						];
						$form->js(null,$js)->univ()->successMessage("Verify Successfully")->execute();
					}
				}
				
				if($_GET['actiontype'] === "Canclled"){
					// $p->add('View_Info')->set('Todo Table Booking Information'.$_GET['actiontype']." = ".$_GET['actionon']);

					$form = $p->add('Form');
					$canceled_reason = $form->addField('DropDown','canceled_reason')->validateNotNull();
					$canceled_reason->setModel($p->add('Model_CancledReason'));
					$canceled_reason->setEmptyText("Please Select Cancled Reason");

					$form->addSubmit('Canclled');
					if($form->isSubmitted()){
						
						$reserved_table = $this->add('Model_ReservedTable')->load($_GET['actionon']);
						$reserved_table->canceled('host',$form['canceled_reason']);
						
						$js = [
							$form->js()->closest('.dialog')->dialog('close'),
							$grid->js()->_selector('div[data-recordid='.$_GET['actionon'].']')->hide()
						];
						$form->js(null,$js)->univ()->successMessage("Cancled Successfully")->execute();
					}	
				}

			});

		$this_url = $this->api->url(null,['cut_object'=>$this->name]);

		$reserved_table = $this->add('Model_ReservedTable');
		$reserved_table->addCondition('restaurant_id',$host_restaurant->id);
		$reserved_table->addCondition('status',['pending','confirmed','']);
		$reserved_table->addExpression('profile_image_url')->set($reserved_table->refSQL('user_id')->fieldQuery('profile_image_url'));
		
		$this->on('click','.hungry-action',function($js,$data)use($this_url,$vp){
				if($data['actiontype'] == "Approve"){
					$reserved_table = $this->add('Model_ReservedTable')->load($data['tablereservationid']);
					$reserved_table->approved();
					$reserved_table->sendReservedTable(true,true);
					return $this->js(null,$this->js()->univ()->successMessage('Reservation Approved'))->reload(['selectedmenu'=>"TableReservation"],null,$this_url);
				}

				$js = [
					$js->univ()->frameURL($data['actiontype'],$this->api->url($vp->getURL(),['actiontype'=>$data['actiontype'],'actionon'=>$data['tablereservationid']]))
                ];
            return $js;
		});

		$this->setModel($reserved_table);

		$quick_search = $this->add('QuickSearch',null,'quick_search')
				            ->useWith($this)
				            ->useFields(['mobile','email','name','discount_coupon','created_at']);	

		$paginator = $this->add("Paginator",null,'Paginator');
        $paginator->setRowsPerPage(10);
	}

	function formatRow(){
		$this->current_row_html['created_at'] = date('(D) d-M-Y',strtotime($this->model['created_at']));
		$this->current_row_html['created_time'] = date('h:i:s A',strtotime($this->model['created_at']));
		if($this->model['restoffer_id'])
			$this->current_row_html['discount_name'] = "Offer";
		else
			$this->current_row_html['discount_name'] = "Discount";
		
		if($this->model['status'] == "confirmed"){
			$this->current_row_html['button_wrapper'] = '<div style="padding-left:0px;" class="col-md-12 col-sm-12 col-lg-12"><div data-actiontype="Verify" data-tablereservationid="'.$this->model->id.'" class="btn btn-primary btn-block hungry-action atk-swatch-orange">Verify</div></div>';
			$this->current_row_html['cancled_button_wrapper'] = " ";
		}else{
			$this->current_row_html['button_wrapper'] = '<div style="padding-left:0px;" class="col-md-6 col-sm-6 col-lg-6"><div data-actiontype="Approve" data-tablereservationid="'.$this->model->id.'" class="btn btn-primary btn-block hungry-action atk-swatch-orange">Approved</div></div>';
			$this->current_row_html['cancled_button_wrapper'] = '<div style="padding-right:0px;" class="col-md-6 col-sm-6 col-lg-6"> <div data-actiontype="Canclled" data-tablereservationid="'.$this->model->id.'" class="btn btn-primary btn-block hungry-action btn-warning">Cancel</div></div>';
		}
		parent::formatRow();
	}

	function setModel($model){
		parent::setModel($model);
	}

	function defaultTemplate(){
		return ['view/hostaccount/tablereservationlister'];
	}
}