<?php

/**
 * Created by Konstantin Kolodnitsky
 * Date: 25.11.13
 * Time: 14:57
 */
class page_index extends Page {

    public $title='Dashboard';

    function init() {
        parent::init();


        $model = $this->add('Model_Voucher');
        $crud = $this->add('CRUD');
        $crud->setModel($model);

        $used_model = $this->add('Model_VoucherUsed');
        $crud = $this->add('CRUD');
        $crud->setModel($used_model);
        
    }
}
