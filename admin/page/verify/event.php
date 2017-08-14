<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_verify_event extends Page {

    public $title='Listing Verification';

    function init() {
        parent::init();

        $this->api->stickyGET('type');
        $this->api->stickyGET('id');

        $id = $_GET['id'];
        $host_business_type = $_GET['type'];

        $search_btn = $this->add('Button')->set('Show/Hide search form')->addClass('atk-swatch-yellow');

        $rest_to_be_verified = $this->add('Model_Event')
                    ->addCondition('is_active',false)
                    ->addCondition('is_verified',false)
                    ;       
        $search_box = $this->add('View')->addClass('atk-box')->setStyle('display','none');
        $search_form = $search_box->add('Form');
        $rest_field = $search_form->addField('DropDown','event_to_be_verify')->validateNotNull();
        $rest_field->setModel($rest_to_be_verified);
        $rest_field->setEmptyText('Please Select Event To Be Verify');
        $search_form->addSubmit('Go');

        $search_btn->js('click',$search_box->js()->toggle());

        $detail_view = $this->add('View');


        if($search_form->isSubmitted()){
            $detail_view->js()->univ()->reload(['id'=>$search_form['event_to_be_verify']])->execute();
        }

        $listing_model = $this->add('Model_event')->load($id);

        $this->title = 'Event Verification of "'.$listing_model['name'].'" ';

        $event_model = $listing_model;

        $tab = $this->add('Tabs');
        $basic_info_tab = $tab->addTab('Basic Info');
        $image_gallery_tab = $tab->addTab('Image Gallery');
        $eventday_tab = $tab->addTab('Event Day');
        $ticket_tab = $tab->addTab('Ticket');


        $email_tab = $tab->addTab('Send Verification Email');
        // Email Setting
        $email_template = $this->add('Model_EmailTemplate')
                            ->addCondition('name',"WELCOMEEMAILEVENT")->tryLoadAny();
        $subject = $email_template['subject'];
        $body = $email_template['body'];

        $user_model = $this->add('Model_User')->addCondition('id',$listing_model['user_id']);

        $body = str_replace("{event_name}", $listing_model['name'], $body);
        $body = str_replace("{address}", $listing_model['address'], $body);
        
        if($user_model->loaded() and $user_model['email']){
            $body = str_replace("{owner_name}", $user_model['name'], $body);
            $body = str_replace("{email_id}", $user_model['email'], $body);
        }else{
            $email_tab->add('View')->addClass('atk-box atk-swatch-red')->set("Host Email Not Found");
        }

        $verification_form = $email_tab->add('Form');
        $verification_form->addSubmit('Verify & send Verification Email');
        if($verification_form->isSubmitted()){
            if(!$listing_model->loaded())
                throw new \Exception("model not loaded", 1);

            $listing_model['is_active'] = 1;
            $listing_model['is_verified'] = 1;
            $listing_model->save();

            if($user_model->loaded() && $user_model['email']){
                try{
                    $outbox = $this->add('Model_Outbox');
                    $outbox->sendEmail($user_model['email'],$subject,$body,$user_model);
                }catch(Exception $e){
                   $verification_form->js()->univ()->errorMessage('event verify but welcome email not send')->execute();
                }                
            }else{
                $verification_form->js()->univ()->errorMessage('event verify but welcome email not send, because host email not found')->execute();
            }

            $verification_form->js()->univ()->successMessage('event Verify Successfully and welcome mail send')->execute();
        }

        $email_tab->add('View')->setHtml($body);

        
        // Basic Form
        $basic_form = $basic_info_tab->add('Form');
        $basic_form->setModel($event_model,
                            [
                                'country',
                                'country_id',
                                'state',
                                'state_id',
                                'city_id',
                                'city',
                                'area_id',
                                'area',
                                'logo_image_id',
                                'logo_image',
                                'banner_image_id',
                                'banner_image',
                                'display_image_id',
                                'display_image',
                                'name',
                                'owner_name',
                                'detail',
                                'address',
                                'mobile_no',
                                'phone_no',
                                'email',
                                'website',
                                'facebook_page_url',
                                'instagram_page_url',
                                'starting_date',
                                'starting_time',
                                'closing_date',
                                'closing_time',
                                'longitude',
                                'latitude',
                                'guidelines',
                                'how_to_reach',
                                'disclaimer',
                                'is_free_ticket',
                                'registration_url'
                            ]);

        $basic_form->addSubmit("Save");
        if($basic_form->isSubmitted()){
            $basic_form->save();
            $basic_form->js()->univ()->successMessage("Saved Successfully")->execute();
        }

        $crud = $image_gallery_tab->add('CRUD');
        $event_image = $this->add('Model_EventImage')
                        ->addCondition('event_id',$event_model->id)
                        ;

        $crud->setModel($event_image,['image_id','image','status','is_active'],['image','status','is_active']);
        $crud->grid->addPaginator(10);
        $crud->grid->addHook('formatRow',function($g){
            if($g->model['image_id']){
                $f = $this->add('filestore/Model_File')->addCondition('id',$g->model['image_id']);
                $f->tryLoadAny();
                if($f->loaded()){
                    $path = $this->app->getConfig('imagepath').str_replace("..", "", $f->getPath());
                    $g->current_row_html['image'] = "<img width='100px' src=".$path.">";
                }else
                    $g->current_row_html['image'] = "No Icon Found";
            }else
                $g->current_row_html['image'] = "No Icon Found";
        });


        $event_day_model = $eventday_tab->add('Model_Event_Day')->addCondition('event_id',$event_model->id);
        $day_crud = $eventday_tab->add('CRUD');
        $day_crud->setModel($event_day_model);
        $day_crud->grid->add('VirtualPage')
            ->addColumn('event_time')
            ->set(function($page)use($event_model){
                $event_day_id = $_GET[$page->short_name.'_id'];

                $time_crud = $page->add('CRUD');
                $time_model = $page->add('Model_Event_Time')
                                ->addCondition('event_day_id',$event_day_id)
                                ->addCondition('event_id',$event_model->id)
                                ;
                $time_crud->setModel($time_model,['name'],['event_day','name','on_date']);
            });
        $day_crud->grid->addPaginator(10);

        // Ticket
        $ticket_crud = $ticket_tab->add('CRUD');
        $event_time_model = $ticket_tab->add('Model_Event_Time')
                            ->addCondition('event_id',$event_model->id)
                            ;
        $ticket_crud->setModel($event_time_model,['name'],['event_day','name','on_date']);
        $ticket_crud->grid->add('VirtualPage')
            ->addColumn('event_ticket')
            ->set(function($page)use($event_model){
                $event_time_id = $_GET[$page->short_name.'_id'];

                $ticket_crud = $page->add('CRUD');
                $ticket_model = $page->add('Model_Event_Ticket')
                                ->addCondition('event_time_id',$event_time_id)
                                ->addCondition('event_id',$event_model->id)
                                ;
                $ticket_crud->setModel($ticket_model,
                                        ['event_id','event_time_id','name','price','detail','offer','applicable_offer_qty','offer_percentage','max_no_to_sale','disclaimer'],
                                        ['name','price','offer_percentage']
                                    );
            });
        $ticket_crud->grid->addPaginator(10);
    }
}
