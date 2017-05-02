<?php
class page_discount extends Page{

    function init(){
        parent::init();

        $this->app->stickyGET('restaurant_id');
        if(!$this->app->city_id){
            $this->add('View_Error',null,'restaurant')->set("city not found");
            return;
        }

        $form = $this->add('Form',null,'discount');
        $discount_model = $this->add('Model_Discount');

        $form->addField('checkbox','discount_0','Clear All')->addClass('discount')->setAttr('data-discount',0);
        foreach ($discount_model as $d_model) {
            $form->addField('checkbox','discount_'.$d_model['name'],$d_model['name'].' % Discount')->addClass('discount')->setAttr('data-discount',$d_model['name']);
        }

        if($_GET['discount']  and !is_numeric($_GET['discount'])){
            $this->add('View_Warning',null,'restaurant')->set('discount not found');
            return;
        }

        if($_GET['discount'])
            $form->getElement('discount_'.$_GET['discount'])->set(true);
        $view = $this->add('View',null,'restaurant');
        $list = $view->add('View_Lister_Restaurant',['item_in_row'=>3,'show_discount'=>true]);
        $model = $this->add('Model_Restaurant')
                    ->addCondition('status','active')
                    ->addCondition('is_verified',1)
                    ->addCondition('city_id',$this->app->city_id)
                    ;
        $model->addExpression('discount_available')->set(function($m,$q){
            return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[
                                        $m->getElement('discount_percentage'),
                                        $m->getElement('discount_subtract')
                                    ]);
        });

        $model->addCondition('discount_available',">",0);

        if($_GET['discount'])
            $model->addCondition('discount_available',$_GET['discount']);

        $list->setModel($model);

        // //Jquery For restaurant
        $form->on('change','.discount',function($js,$data){
            if($data['discount'])
                return $js->univ()->location($this->app->getConfig('absolute_url').'discount/'.$this->app->city_name.'/'.$data['discount']);
            else
                return $js->univ()->location($this->app->getConfig('absolute_url').'discount/'.$this->app->city_name);
        });


        $discount_view = $this->add('View',null,'getdiscount');
        if($_GET['restaurant_id']){
            $discount_view_discount = $discount_view->add('View_Restaurant_GetDiscount',['restaurant_id'=>$_GET['restaurant_id']]);
        }
        $discount_view_url = $this->app->url(null,['cut_object'=>$discount_view->name]);
        $this_name = $this->name;

        $view->on('click','.hungry-getdiscount',function($js,$data)use($discount_view,$discount_view_url,$this_name){
            return [
                    $js->_selector("#".$this_name."discount_model")->modal('show'),
                    $discount_view->js()->reload(['restaurant_id'=>$data['restaurantid']],null,$discount_view_url)
                ]                    
                    ;
        });
    }

    function defaultTemplate(){
    	return ['page/discount'];
    }

}

