<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_space extends Page {

    public $title='Space';

    function init() {
        parent::init();
        
        if(!$destination_id = $this->api->stickyGET('destination_id')){
        	$this->add('View_Error')->set('Desination not Found');
        	return;
        }
        
        $crud = $this->add('CRUD');
        $dest_model = $this->add('Model_Destination_Space')->addCondition('destination_id',$destination_id);
        $crud->setModel($dest_model,array('name','cps','size','type','image_id','image','is_active'));

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
