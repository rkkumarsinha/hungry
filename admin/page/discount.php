<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_discount extends page_adminrestaurant {

    public $title='Discount';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $offer_model = $this->add('Model_Discount');
        $offer_model->setOrder('id','desc');
        
        $c->setModel($offer_model);
        $c->grid->addQuickSearch(['name']);
        $c->grid->addPaginator(30);
    }

}
