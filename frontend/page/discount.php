<?php
class page_discount extends Page{

    function init(){
        parent::init();

        $form = $this->add('Form',null,'discount');
        $discount_field_10 = $form->addField('checkbox','discount_10','10 % Discount')->addClass('discount')->setAttr('data-discount','10');
        $discount_field_15 = $form->addField('checkbox','discount_15','15 % Discount')->addClass('discount')->setAttr('data-discount','15');
        $discount_field_20 = $form->addField('checkbox','discount_20','20 % Discount')->addClass('discount')->setAttr('data-discount','20');
        $discount_field_25 = $form->addField('checkbox','discount_25','25 % Discount')->addClass('discount')->setAttr('data-discount','25');
        $discount_field_30 = $form->addField('checkbox','discount_30','30 % Discount')->addClass('discount')->setAttr('data-discount','30');

        if($_GET['discount']  and !is_numeric($_GET['discount'])){
        	$this->add('View_Warning',null,'restaurant')->set('something happen wrong');
        	return;
        }

        if($_GET['discount'])
        	$form->getElement('discount_'.$_GET['discount'])->set(true);
        $view = $this->add('View',null,'restaurant');
        // $view->set('hello');
        $list = $view->add('View_Lister_Restaurant',['item_in_row'=>3,'show_discount'=>true]);
        $model = $this->add('Model_Restaurant')->addCondition('banner_image_id','<>',null);
       	if($_GET['discount'])
       		$model->addCondition('discount',$_GET['discount']);
        $list->setModel($model);

      	//Jquery For Filter the images
        $form->on('change','.discount',function($js,$data){
        	return $js->univ()->location('?page=discount&discount='.$data['discount']);
        });

        $view->on('click','.hungry-getdiscount',function($js,$data){
            return $js->univ()->frameURL('Get Discount',$this->api->url('getdiscount',array('restaurant_id'=>$data['restaurantid'],'cut_page'=>0)));
        });
    }

    function defaultTemplate(){
    	return ['page/discount'];
    }

}

