<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_package extends Page {

    public $title='Package';

    function init() {
        parent::init();

        if(!$destination_id = $this->api->stickyGET('destination_id')){
        	$this->add('View_Error')->set('Desination not Found');
        	return;
        }

        $crud = $this->add('CRUD');
        $crud->setModel($this->add('Model_Destination_Package')->addCondition('destination_id',$destination_id));
        
        $crud->grid->addPaginator($ipp=10);
        $crud->grid->addQuickSearch(['name','price']);
    }

}
