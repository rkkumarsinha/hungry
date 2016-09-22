<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_review extends Page {

    public $title='Review';

    function init() {
        parent::init();

        if(!$destination_id = $this->api->stickyGET('destination_id')){
        	$this->add('View_Error')->set('Desination not Found');
        	return;
        }

        $crud = $this->add('CRUD');
        $model = $this->add('Model_Review')->addCondition('destination_id',$destination_id);
        $crud->grid->add('VirtualPage')
            ->addColumn('Comments')
            ->set(function($page){
            $id = $_GET[$page->short_name.'_id'];
            $comment_model = $this->add('Model_Comment')->addCondition('review_id',$id)->addCondition('is_approved',true);
            
            $page->add('CRUD')->setModel($comment_model);
        });

        $crud->setModel($model);
    }

}
