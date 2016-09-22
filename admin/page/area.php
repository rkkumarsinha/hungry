<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_area extends Page {

    public $title='Area';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $area_model = $this->add('Model_Area');
        $area_model->setOrder('id','desc');
        
        $c->setModel($area_model);
        $c->grid->addQuickSearch(['name','city','state','country']);
        $c->grid->addPaginator(10);
    }

}
