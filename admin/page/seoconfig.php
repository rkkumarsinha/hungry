<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_seoconfig extends page_adminconfiguration {

    public $title='Meta Config';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $crud->setModel('SeoConfig');

        $crud->grid->add('VirtualPage')
		      ->addColumn('meta_tags')
		      ->set(function($page){
		          $id = $_GET[$page->short_name.'_id'];
                  $meta_tag_model = $page->add('Model_MetaTag');
                  $meta_tag_model->addCondition('seo_config_id',$id);
                  $crud = $page->add('CRUD');
                  $crud->setModel($meta_tag_model);
		      });
    }

}
