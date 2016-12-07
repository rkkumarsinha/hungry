<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_reservedtable extends page_adminrestaurant {

    public $title='Reserved Table';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $model = $this->add('Model_ReservedTable');
        
        $temp = ['restaurant_address','restaurant_image'];
        foreach ($temp as $key => $field_name) {
            $model->getElement($field_name)->destroy();
        }

        $model->setOrder('created_at','desc');
        $crud->setModel($model);

        $grid = $crud->grid;
        $grid->addPaginator($ipp=50);
        $grid->addQuickSearch(['user','booking_id','email','mobile']);
    }

}
