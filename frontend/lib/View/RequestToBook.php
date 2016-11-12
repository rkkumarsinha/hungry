<?php

class View_RequestToBook extends View{

	function init(){
		parent::init();			

		$destination_id = $_GET['destination_id'] =  $this->api->stickyGET('destination_id');
        if(!$this->api->auth->model->id){
            $this->add('View_Login',['reload'=>"parent"]);
            $this->js(true)->_selector('.requesttobook-hungry-submit')->hide();
            return;
        }

        if(!$destination_id){
        	$this->add('View_Error')->set('Destination not found');
            return;
        }else
        	$this->js(true)->_selector('.requesttobook-hungry-submit')->show();

        $destination = $this->add('Model_Destination')->tryLoad($destination_id);
        // $v = $this->add('View');
        if($_GET['enquiry_id']){
            if($_GET['enquiry_id'] == "delete"){
                $this->add('View_Error')->set('please try agin, failed due to technical or internet connection');
            }else{
                $this->add('View_Success')->setHtml('Hi '.$this->app->auth->model['name'].'<br/> we have received your booking request. our representative will shortly contact you and confirm your booking.');
            }
            $this->js(true)->_selector('.requesttobook-hungry-submit')->hide();
            return;
        }

        $form = $this->add('Form',null,null,['form/stacked']);
        $c = $form->add('Columns');
        $c1 = $c->addColumn(6);
        $c2 = $c->addColumn(6);

        // $c1->addField('Radio',"offers",'Flat Discount and Offers')//->setValueList($restaurant->getOfferAndDiscount())->validateNotNull();
        $row = $c1->add('Columns');
        $row1_col1 = $row->addColumn(6);
        $row1_col2 = $row->addColumn(6);

        $row1_col1->addField('line','name')->set($this->api->auth->model['name']);
        $row1_col2->addField('line','mobile')->set($this->api->auth->model['mobile']);
        
        $c1->addField('line','email')->set($this->api->auth->model['email']);

        $row = $c1->add('Columns');
        $row_col1 = $row->addColumn(3);
        $row_col2 = $row->addColumn(3);
        $row_col3 = $row->addColumn(6);

        $row_col1->addField('Number','adult')->validateNotNull();
        $row_col2->addField('Number','child')->validateNotNull();
        $row_col3->addField('Number','budget_per_persion','Budget Per Person')->validateNotNull();

        
        $row2 = $c1->add('Columns')->addClass('input-padding-remove');
        $row2_col1 = $row2->addColumn(6);
        $row2_col2 = $row2->addColumn(4);
        $row2_col3 = $row2->addColumn(2);

        $date_picker = $row2_col1->addField('DatePicker','booking_date')->validateNotNull()->addClass('padding-reset');
        $time = $row2_col2->addField('dropdown','time')->validateNotNull()->setEmptyText('Please select');
        $time_array = [];
        $hour = 00;
        $minute = 00;
        for ($hour = 00; $hour < 12; $hour++) { 
            for ($minute = 0; $minute <= 60 ; $minute= $minute + 15) {
                $value = $hour." : ".$minute;
                $time_array[$value] = $value;
            }
        }
        $time->setValueList($time_array);

        $row2_col3->addField('dropdown','period')->setValueList(['AM'=>'AM','PM'=>'PM']);

        $occassion = $c1->addField('dropdown','occassion');
        $occassion->validateNotNull()->setValueList($destination->occassionList());
        $occassion->setEmptyText('Please Select');
        
        $c2->addField('radio','packages')->setValueList($destination->packageList())->validateNotNull();
        $c2->addField('text','request')->setAttr('PlaceHolder','Your Special Request (optional)');
        $c2->addField('Checkbox','agree_with_terms_and_conditions')->set(true);
        $c2->addField('Checkbox','my_dates_are_flexible');

        $this->js('click',$form->js()->submit())->_selector('.requesttobook-hungry-submit');

        if($form->isSubmitted()){
            if(!$form['agree_with_terms_and_conditions'])
                $form->displayError('agree_with_terms_and_conditions','you must agree with our terms and condition');

            $enquiry_model = $this->add('Model_DestinationEnquiry');
            $enquiry_model['user_id'] = $this->api->auth->model->id;
            $enquiry_model['destination_id'] = $destination->id;
            $enquiry_model['name'] = $form['name'];
            $enquiry_model['adult'] = $form['adult'];
            $enquiry_model['child'] = $form['adult'];
            $enquiry_model['email'] = $form['email'];
            $enquiry_model['mobile'] = $form['mobile'];
            $enquiry_model['created_at'] = $form['booking_date'];
            $enquiry_model['created_time'] = date("H:i", strtotime($form['time']." ".$form['period']));
            $enquiry_model['message'] = $form['remark'];
            $enquiry_model['total_budget'] = $form['budget_per_persion'];
            $enquiry_model['status'] = "pending";
            $enquiry_model['pakage_id'] = $form['packages'];
            $enquiry_model['occassion_id'] = $form['occassion'];

            try{
        		$enquiry_model->save();
            	// $enquiry_model->sendRequestToBook($enquiry_model['email'],$enquiry_model['mobile']);
       			$this->js()->univ()->reload(['enquiry_id'=>$enquiry_model['id']])->execute();
            }catch(\Exception $e){
                $enquiry_model->delete();
                $this->js()->univ()->reload(['enquiry_id'=>"delete"])->execute();
            }

            $this->js(true)->_selector('.requesttobook-hungry-submit')->hide();
            
        }

	}
}