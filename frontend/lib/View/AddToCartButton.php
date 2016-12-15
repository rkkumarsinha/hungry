<?php

class View_AddToCartButton extends View{
	public $option= ['show_image'=>true];
	function init(){
		parent::init();

	}

	function setModel($m){
		parent::setModel($m);
		
		if(!$m['remaining_ticket']){
			$this->add('View_Info',null,'addtocartform')->set("All Ticket Booked");

		}else{
			$form = $this->add('Form',null,"addtocartform",array('form/stacked'));
			$form->setLayout('form\eventaddtocart');
			$price_field = $form->addField('Readonly','price')->set($m['price']);

			$qty_field = $form->addField('Spinner','quantity');
			$qty_field->min = 1;
			$qty_field->max = $m['remaining_ticket'];
			
			$qty_field->validateNotNull(true)->set(1)->addClass('hungrySpinner');
			// $qty_field->js(true)->spinner(array('min'=>1,'max'=>$m['remaining_ticket'],"step"=>1));

			$amount_field = $form->addField('Readonly','amount')->set($m['price']);

			$book_now_button = $form->layout->add('Button',null,'submit_button')->addClass('atk-swatch-orange btn-block')->set('Book Now');
			$book_now_button->js('click',$form->js()->submit());

			if($this->app->stickyGET('hungry_event_qty')){
				$amount_field->set($m['price'] * $_GET['hungry_event_qty']);
			}
			
			$qty_field->js('change',$amount_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$amount_field->name]),'hungry_event_qty'=>$qty_field->js()->val()]));

			if($form->isSubmitted()){
				//check for max number to qty
				if($form['quantity'] > $m['remaining_ticket'])
					$form->error('quantity','cannot select more remaining quantity');
				
				$cart = $this->add('Model_Cart');
				$cart->addTicket($m->id,$m['name'],$form['quantity'],$m['event_time_id'], $m['event_time'],$m['event_day_id'],$m['event_day'],$m['price'],$m['disclaimer']);

				$form->js(null,$this->js()->_selector('.hungrycart')->trigger('reload'))->univ()->successMessage("Ticket added into your wallet")->execute();
			}
			
		}

		$ticket = $this->add('Model_Event_Ticket')->tryLoad($m->id);
		if($ticket->loaded()){
			$f = $this->add('filestore/Model_File')->load($ticket['display_image_id']);
			$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
		}
		
		$this->template->trySet('name',$m['name']);
		$this->template->trySet('detail',$m['detail']);
		$this->template->trySet('remaining_ticket',$m['remaining_ticket']);
		$this->template->trySet('price',$m['price']);
		$this->template->trySet('first_image',$path);
		$this->template->trySet('disclaimer',$m['disclaimer']);
		
		$remaining_count_array = str_split($m['remaining_ticket']);
		$remaining_count_lister = $this->add('CompleteLister',null,"remaining_count",array('view/addtocartbutton','remaining_count'));
		$remaining_count_lister->setSource($remaining_count_array);

		$ribben = $this->add('View',null,'ribben');
		$ribben_status = "in-queue";
		$ribben_color = "fa fa-clock-o";

		if(!$m['remaining_ticket']){
			$ribben_color = "fa fa-check";
			$ribben_status = "approved";
		}

		$ribben->setHtml('<div class="ribbon '.$ribben_status.'"><i class="'.$ribben_color.'"></i></div>');
	}

	function defaultTemplate(){
		return ['view/addtocartbutton'];
	}

}