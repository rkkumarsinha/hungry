<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_verify extends Page {

    public $title='To be Verify';

    function init() {
        parent::init();

        $c = $this->add('Columns');
        $rest = $c->addColumn(4);
        $event = $c->addColumn(4);
        $destination = $c->addColumn(4);


        $rest_to_be_verified = $this->add('Model_Restaurant')
                            ->addCondition('status','deactive')
                            ->addCondition('is_verified',false)
                            ;

        $event_to_be_verified = $this->add('Model_Event')
                            ->addCondition('is_active',false)
                            ->addCondition('is_verified',false)
                            ;

        $dest_to_be_verified = $this->add('Model_Destination')
                            ->addCondition('status',"deactive")
                            ->addCondition('is_verified',false)
                            ;
        
        $rest->add('View_Box')->set("Restaurant ".$rest_to_be_verified->count()->getOne())->addClass('atk-swatch-yellow');
        $event->add('View_Info')->set("Event ".$event_to_be_verified->count()->getOne());
        $destination->add('View_Success')->set("Destination ".$dest_to_be_verified->count()->getOne());

        $tab = $this->add('Tabs');
        
        $rest_to_be_verified->setOrder('created_at','desc');

        $rest_tab = $tab->addTab('Restaurant');
        $rest_crud = $rest_tab->add('Grid');
        $rest_crud->setModel($rest_to_be_verified,['name','address']);

        $rest_crud->addColumn('detail');
        $rest_crud->addHook('formatRow',function($g){
            $g->current_row_html['detail'] = '<a target="_blank" class="atk-button-small" href="'.$this->api->url('verify_rest',['type'=>'restaurant','id'=>$g->model['id']]).'">Detail</a>';
        });
        $rest_crud->addPaginator($ipp = 10);
        $rest_crud->addQuickSearch(['name']);

        $event_to_be_verified->setOrder('created_at','desc');
        $event_tab = $tab->addTab('Event');
        $event_crud = $event_tab->add('Grid');
        $event_crud->setModel($event_to_be_verified,['name','address']);
        
        $event_crud->addColumn('detail');
        $event_crud->addHook('formatRow',function($g){
            $g->current_row_html['detail'] = '<a target="_blank" class="atk-button-small" href="'.$this->api->url('verify_event',['type'=>'event','id'=>$g->model['id']]).'">Detail</a>';
        });

        $event_crud->addPaginator($ipp = 10);
        $event_crud->addQuickSearch(['name']);

        $dest_to_be_verified->setOrder('created_at','desc');
        $destination_tab = $tab->addTab('Destination');
        $destination_crud = $destination_tab->add('Grid');
        $destination_crud->setModel($dest_to_be_verified,['name','address']);
        $destination_crud->addColumn('detail');
        $destination_crud->addHook('formatRow',function($g){
            $g->current_row_html['detail'] = '<a target="_blank" class="atk-button-small" href="'.$this->api->url('verify_destination',['type'=>'destination','id'=>$g->model['id']]).'">Detail</a>';
        });

        $destination_crud->addPaginator($ipp = 10);
        $destination_crud->addQuickSearch(['name']);

    }

    function page_restaurant(){

    }
}
