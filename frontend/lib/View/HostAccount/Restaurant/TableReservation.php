<?php

class View_HostAccount_Restaurant_TableReservation extends CompleteLister{

	function init(){
		parent::init();

		if(!$this->app->listmodel->loaded())
			throw new \Exception("list model not found");

		$host_restaurant = $this->app->listmodel;
		$this->api->stickyGET('actionon');
		$this->api->stickyGET('actiontype');

		$grid = $this;
		$vp = $this->add('VirtualPage')->set(function($p)use($grid){
				if(!$_GET['actionon']){
					$p->add('View_Error')->set("action on not exist");
					return;
				}

				if($_GET['actiontype'] === "Approve"){
					$p->add('View_Info')->set('Todo Table Booking Information'.$_GET['actiontype']." = ".$_GET['actionon']);
					$form = $p->add('Form');
					$form->addSubmit('Approved');
					if($form->isSubmitted()){
						$reserved_table = $this->add('Model_ReservedTable')->load($_GET['actionon']);
						$reserved_table->approved();
						
						$js = [
							$form->js()->closest('.dialog')->dialog('close'),
							$grid->js()->_selector('div[data-recordid='.$_GET['actionon'].']')->hide()
						];
						$form->js(null,$js)->univ()->successMessage("Approved Successfully")->execute();
					}
				}
				if($_GET['actiontype'] === "Canclled"){
					$p->add('View_Info')->set('Todo Table Booking Information'.$_GET['actiontype']." = ".$_GET['actionon']);

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
		$reserved_table->addCondition('status','pending');
		$reserved_table->addExpression('profile_image_url')->set($reserved_table->refSQL('user_id')->fieldQuery('profile_image_url'));
		
		$this->on('click','.hungry-action',function($js,$data)use($this_url,$vp){
				$js = [
					$js->univ()->frameURL($data['actiontype'],$this->api->url($vp->getURL(),['actiontype'=>$data['actiontype'],'actionon'=>$data['tablereservationid']]))
                    // $this->js()->reload(],null,$this_url)
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
				
		parent::formatRow();
	}

	function setModel($model){
		parent::setModel($model);
	}

	function defaultTemplate(){
		return ['view/hostaccount/tablereservationlister'];
	}
}