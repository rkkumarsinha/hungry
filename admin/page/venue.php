<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_venue extends Page {

    public $title='Venue';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $venue_model = $this->add('Model_Venue');
        $venue_model->setOrder('sequence_order','Asc');
        $c->setModel($venue_model);

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
