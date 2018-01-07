<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_blog extends Page {

    public $title='Blog';

    function page_index() {

        $tab = $this->add('Tabs');
        $tab->addTabUrl('./category','Blog Category');
        $tab->addTabUrl('./post','Blog Post');
    }

    function page_category(){
        $cat = $this->add('Model_BlogCategory');
        $cat->setOrder('is_active','desc');
        $this->add('CRUD')->setModel($cat);
    }


    function page_post(){
        $cat = $this->add('Model_BlogPost');
        $cat->setOrder('is_active','desc');

        $crud = $this->add('CRUD');
        $crud->setModel($cat);
           
    }
}
