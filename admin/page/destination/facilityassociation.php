<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_facilityassociation extends Page {

    public $title='Facility';

    function init() {
        parent::init();

        if(!$destination_id = $this->api->stickyGET('destination_id')){
        	$this->add('View_Error')->set('Desination not Found');
        	return;
        }

        $model = $this->add('Model_Destination_HighlightAssociation')
                ->addCondition('destination_id',$destination_id)
                ->addCondition('highlight_type',"facility");

        $crud = $this->add('CRUD');
        $crud->setModel($model);

        if($crud->isEditing()){
            $highlight_field = $crud->form->getElement('destination_highlight_id');
            $highlight_model = $highlight_field->getModel();
            $highlight_model->addCondition('type','facility');
        }

        $crud->grid->addHook('formatRow',function($g){

            if($g->model['icon_url']){
                $g->current_row_html['icon_url'] = "<img style='max-width:100px;' src=".$g->model['icon_url'].">";
            }else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });
        
        $crud->grid->addPaginator($ipp=50);
        $crud->grid->addQuickSearch(['destination_highlight']);
    }

}
