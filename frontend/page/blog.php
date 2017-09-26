<?php

class page_blog extends Page{

    function init(){
        parent::init();
        
        $model = $this->add('Model_BlogPost');
        $lister = $this->add('View_Lister_PostList',null,'post_list');
        $lister->setModel($model);
    }

    function defaultTemplate(){
        return ['page/blog'];
    }
}