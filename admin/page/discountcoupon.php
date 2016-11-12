<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_discountcoupon extends Page {

    public $title='Discount Coupon';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $model = $this->add('Model_DiscountCoupon');
        $model->setOrder('created_at','Desc');

        $crud->setModel($model);
		$crud->grid->addPaginator($ipp=50);
		$crud->grid->addQuickSearch(['name','email','discount_coupon','mobile']);
    }

}
