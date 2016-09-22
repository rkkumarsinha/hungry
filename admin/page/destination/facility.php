<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_facility extends Page {

    public $title='Facility';

    function init() {
        parent::init();

        $crud = $this->add('CRUD');
        $crud->setModel('Destination_Facility');
        $crud->grid->addHook('formatRow',function($g){

			if($g->model['image_id']){
				$f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
				$f->tryLoadAny();
				if($f->loaded()){
					$path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
					$g->current_row_html['image'] = "<img style='max-width:100px;' src=".$path.">";
				}else
					$g->current_row_html['image'] = "No Icon Found";
			}else
				$g->current_row_html['image'] = "No Icon Found";
		});
    }

}
