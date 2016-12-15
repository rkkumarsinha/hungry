<?php

class View_CartDetail extends CompleteLister{
	
	function init(){
		parent::init();
		
		$btn = $this->add('Button',null,'empty_cart')->set('empty cart');
		if($btn->isClicked()){
			$this->add('Model_Cart')->emptyCart();
			$this->js(null,$this->js()->univ()->reload())->univ()->successMessage('cart empty successfully')->execute();
		}

		$this->on('click','.hungry-remove-event-cart-item',function($js,$data){
			if($js->univ()->confirm('Are you sure?')){
				$cart = $this->add('Model_Cart')->load($data['cartid'])->delete();
				return $js->location();
			}
			
			return $js->univ()->consoleError('good choice not delete');
		});

	}
	
	function setModel($m){
		parent::setModel($m);
		
		if(!$m->count())
			$this->template->tryDel('checkout_button');
	}

	function formatRow(){
		$m = $this->model;
		
		if(!$m['event_ticket_id']){
			parent::setModel($m);
			return;
		}

		$event_ticket_model = $this->add('Model_Event_Ticket')->tryLoad($m['event_ticket_id']);
		$event_ticket_model->addExpression('event_day_id')->set(function($m,$q){
			return $m->refSQL('event_time_id')->fieldQuery('event_day_id');
		});

		if($event_ticket_model->loaded()){
			$f = $this->add('filestore/Model_File')->tryLoad($event_ticket_model['display_image_id']);
			$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
		}

		$form = $this->add('Form',null,"addtocartform",array('form/stacked'));
		$form->setLayout('form\eventaddtocart');

		//cart id
		$form->addField('Hidden','cartid')->set($m->id);
		// price
		$price_field = $form->addField('Readonly','price')->set($event_ticket_model['price']);
		//quantity field
		$qty_field = $form->addField('Number','quantity')->validateNotNull(true)->set(1)->addClass('hungrySpinner');
		$qty_field->js(true)->spinner(array('min'=>1,'max'=>$event_ticket_model['remaining_ticket'],"step"=>1));
		$qty_field->set($m['qty']);

		// amount field
		$amount_field = $form->addField('Readonly','amount')->set($m['qty'] * $event_ticket_model['price']);
		$form->layout->add('View',null,'submit_button')->addClass('hungry-remove-event-cart-item btn btn-danger')->set('x')->setAttr('title','remove ticket')->setAttr('data-cartid',$m->id)->setStyle('margin-top','20px');
		// $qty_field->js('change',$amount_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$amount_field->name]),'hungry_event_qty'=>$qty_field->js()->val()]));
		// on chnage on quantity field form submit
		$qty_field->js('change',$form->js()->submit());

		if($form->isSubmitted()){
			//check for max number to qty
			if($form['quantity'] > $event_ticket_model['remaining_ticket'])
				$form->error('quantity','cannot greater then '.$event_ticket_model['remaining_ticket']);
			
			$old_cart = $this->add('Model_Cart')->load($form['cartid']);

			$new_cart = $this->add('Model_Cart');
			$new_cart['event_ticket_id'] = $event_ticket_model['id'];
			$new_cart['name'] = $event_ticket_model['name'];
			$new_cart['qty'] = $form['quantity'];
			$new_cart['event_time_id'] = $event_ticket_model['event_time_id'];
			$new_cart['event_time'] = $event_ticket_model['event_time'];
			$new_cart['event_day_id'] = $event_ticket_model['event_day_id'];
			$new_cart['event_day'] = $event_ticket_model['event_day'];
			$new_cart['unit_price'] = $event_ticket_model['price'];
			$new_cart['disclaimer'] = $event_ticket_model['disclaimer'];
			$new_cart['sequence'] = $old_cart['sequence'];
			$new_cart->save();

			//delete old cart and adding new cart item
			$old_cart->delete();
			$form->js()->redirect($this->api->url())->execute();
		}

		$this->current_row_html['addtocartform'] = $form->getHtml();

		// $this->template->trySet('name',$m['name']);
		// $this->template->trySet('price',$m['price']);
		//remaining ticket to show
		$this->current_row_html['remaining_ticket'] = $event_ticket_model['remaining_ticket'];
		// add display
		$this->current_row_html['detail'] = $event_ticket_model['detail'];
		//add first image
		$this->current_row_html['first_image'] = $path;
		$this->current_row_html['id'] = $this->model->id;
		
		// added remaining ticket count
		$remaining_count_array = str_split($event_ticket_model['remaining_ticket']);
		$remaining_count_lister = $this->add('CompleteLister',null,"remaining_count",array('view/cartdetail','remaining_count'));
		$remaining_count_lister->setSource($remaining_count_array);

		$this->current_row_html['remaining_count'] = $remaining_count_lister->getHtml();
		//add ribben
		$ribben = $this->add('View',null,'ribben');
		$ribben_status = "in-queue";
		$ribben_color = "fa fa-clock-o";

		if(!$event_ticket_model['remaining_ticket']){
			$ribben_color = "fa fa-check";
			$ribben_status = "approved";
		}
		
		$this->current_row_html['ribben'] = '<div class="ribbon '.$ribben_status.'"><i class="'.$ribben_color.'"></i></div>';
		$this->current_row_html['event_day'] = date('(D) d - M - Y',strtotime($m['event_day']));
		$this->current_row_html['event_name'] = $event_ticket_model['event'];
		$this->current_row_html['disclaimer'] = $event_ticket_model['disclaimer'];
		$this->current_row_html['sequence'] = $m['sequence'];

		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/cartdetail'];
	}

}