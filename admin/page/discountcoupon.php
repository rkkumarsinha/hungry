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

        $this->add('CRUD')->setModel('DiscountCoupon');
    }

}
