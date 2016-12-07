<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_venueassociation extends Page {

    public $title='Venue';

    function init() {
        parent::init();

        if(!$destination_id = $this->api->stickyGET('destination_id')){
        	$this->add('View_Error')->set('Desination not Found');
        	return;
        }

        $crud = $this->add('CRUD');
        $crud->setModel($this->add('Model_Destination_VenueAssociation')->addCondition('destination_id',$destination_id),['destination_id','venue_id','icon_url'],['name']);
        $crud->grid->addHook('formatRow',function($g){

            if($g->model['icon_url']){
                $g->current_row_html['icon_url'] = "<img style='max-width:100px;' src=".$g->model['icon_url'].">";
            }else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });

        $crud->grid->addPaginator($ipp=10);
        $crud->grid->addQuickSearch(['name']);
    }

}
