<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_discountcoupon extends page_adminrestaurant {

    public $title='Discount Coupon';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $model = $this->add('Model_DiscountCoupon');

        $temp = ['restaurant_address','restaurant_image','restaurant_name'];
        foreach ($temp as $key => $field_name) {
            $model->getElement($field_name)->destroy();
        }

        $model->setOrder('created_at','Desc');

        $crud->setModel($model);
		$crud->grid->addPaginator($ipp=50);
		$crud->grid->addQuickSearch(['name','email','discount_coupon','mobile','restaurant']);
    }

}
