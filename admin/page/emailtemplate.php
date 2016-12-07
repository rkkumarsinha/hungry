<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_emailtemplate extends page_adminconfiguration {

    public $title='Email Template';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');

        $crud->setModel('EmailTemplate');

        $crud->grid->add('VirtualPage')
		      ->addColumn('view')
		      ->set(function($page){
		          $id = $_GET[$page->short_name.'_id'];
		          $model = $page->add('Model_EmailTemplate')->load($id);
		          $page->add('View')->setHtml($model['body']);
		      });


    }

}
