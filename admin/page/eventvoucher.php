<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_eventvoucher extends page_adminevent {

    public $title='Event Voucher';

    function init() {
        parent::init();


        $model = $this->add('Model_Voucher');
        $model->setOrder('id','desc');
        $crud = $this->add('CRUD');
        $crud->setModel($model);
        $crud->grid->addPaginator(50);
        $crud->grid->addQuickSearch(['name']);

        $crud->addRef('VoucherUsed');

        // $used_model = $this->add('Model_VoucherUsed');
        // $crud = $this->add('CRUD');
        // $crud->setModel($used_model);

    }
}
