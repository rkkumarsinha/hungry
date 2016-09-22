<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_eventcategory extends Page {

    public $title='Event Category';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $area_model = $this->add('Model_Event_Category');
        
        $c->setModel($area_model);

        $c->grid->addHook('formatRow',function($g){

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
