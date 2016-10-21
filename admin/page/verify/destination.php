<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_verify_destination extends Page {

    public $title='Listing Verification';

    function init() {
        parent::init();

        $this->api->stickyGET('type');
        $this->api->stickyGET('id');

        $id = $_GET['id'];
        $host_business_type = $_GET['type'];

        $search_btn = $this->add('Button')->set('Show/Hide search form')->addClass('atk-swatch-yellow');

        $rest_to_be_verified = $this->add('Model_Destination')
                    ->addCondition('status','deactive')
                    ->addCondition('is_verified',false)
                    ;       
        $search_box = $this->add('View')->addClass('atk-box')->setStyle('display','none');
        $search_form = $search_box->add('Form');
        $rest_field = $search_form->addField('DropDown','destination_to_be_verify')->validateNotNull();
        $rest_field->setModel($rest_to_be_verified);
        $rest_field->setEmptyText('Please Select destination To Be Verify');
        $search_form->addSubmit('Go');

        $search_btn->js('click',$search_box->js()->toggle());

        $detail_view = $this->add('View');


        if($search_form->isSubmitted()){
            $detail_view->js()->univ()->reload(['id'=>$search_form['destination_to_be_verify']])->execute();
        }


        $listing_model = $this->add('Model_Destination')->load($id);

        $this->title = 'Destination Verification of "'.$listing_model['name'].'" ';

        $host_destination = $listing_model;
        
        $tab = $detail_view->add('Tabs');
        $basic_info_tab = $tab->addTab('Basic Info');
        $image_gallery_tab = $tab->addTab('Image Gallery');
        $highlight_tab = $tab->addTab('Highlight');
        $category_tab = $tab->addTab('Category');
        $space_tab = $tab->addTab('Space');
        $package_tab = $tab->addTab('Packages');
        $email_tab = $tab->addTab('Send Verification Email');


        $email_template = $this->add('Model_EmailTemplate')
                            ->addCondition('name',"WELCOMEEMAILHOST")->tryLoadAny();
        $subject = $email_template['subject'];
        $body = $email_template['body'];

        $user_model = $this->add('Model_User')->tryLoad($listing_model['user_id']);
        $body = str_replace("{restaurant_name}", $listing_model['name'], $body);
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

            $listing_model['status'] = "active";
            $listing_model['is_verified'] = 1;
            $listing_model->save();

            if($user_model->loaded() && $user_model['email']){
                try{
                    $outbox = $this->add('Model_Outbox');
                    $outbox->sendEmail($user_model['email'],$subject,$body,$user_model);
                }catch(Exception $e){
                   $verification_form->js()->univ()->errorMessage('destination verify but welcome email not send')->execute();
                }                
            }else{
                $verification_form->js()->univ()->errorMessage('destination verify but welcome email not send, because host email not found')->execute();
            }

            $verification_form->js()->univ()->successMessage('Destination Verify Successfully and welcome mail send')->execute();
        }

        $email_tab->add('View')->setHtml($body);

        // Basic Form
        $basic_form = $basic_info_tab->add('Form');
        $basic_form->setModel($host_destination,
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
                                'about_destination',
                                'address',
                                'mobile_no',
                                'phone_no',
                                'email',
                                'website',
                                'facebook_page_url',
                                'instagram_page_url',
                                'rating',
                                'avg_cost',
                                'credit_card_accepted',
                                'reservation_needed',
                                'longitude',
                                'latitude',
                                'monday',
                                'tuesday',
                                'wednesday',
                                'thursday',
                                'friday',
                                'saturday',
                                'sunday',
                                'food_type',
                                'booking_policy',
                                'cancellation_policy',
                                'guidelines',
                                'how_to_reach'
                            ]);

        $basic_form->addSubmit("Update");
        if($basic_form->isSubmitted()){
            $basic_form->save();
            $basic_form->js()->univ()->successMessage("Updated Successfully");
        }

        //Destination gallery images
        $gallary_crud = $image_gallery_tab->add('CRUD');
        $dest_image = $this->add('Model_DestinationImage')
                        ->addCondition('destination_id',$host_destination->id)
                        ->addCondition('type','destination')
                        ;

        $gallary_crud->setModel($dest_image,['image_id','image','status'],['image','status','created_at','approved_date']);
        $gallary_crud->grid->addPaginator(10);
        $gallary_crud->grid->addHook('formatRow',function($g){
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

        // Highlight
        $r_hl = $highlight_tab->add('Model_Destination_HighlightAssociation')->addCondition('destination_id',$host_destination->id);
        $hl_crud = $highlight_tab->add('CRUD');
        $hl_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $hl_crud->setModel($r_hl,['destination_highlight','destination_highlight_id','destination_id'],['destination_highlight','highlight_type','icon_url']);


        // Destination Space
        $space_model = $space_tab->add('Model_Destination_Space')->addCondition('destination_id',$host_destination->id);
        $space_crud = $space_tab->add('CRUD');
        $space_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });
        $space_crud->setModel($space_model,['name','cps','size','type','image_id','is_active'],['name','cps','size','type','is_active','icon_url']);

        // Destination_Package
        $package_model = $package_tab->add('Model_Destination_Package')->addCondition('destination_id',$host_destination->id);
        $package_crud = $package_tab->add('CRUD');
        $package_crud->setModel($package_model,['name','price','detail','is_active']);

        // Destination Space
        $venue_model = $category_tab->add('Model_Destination_VenueAssociation')->addCondition('destination_id',$host_destination->id);
        $venue_crud = $category_tab->add('CRUD');
        $venue_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img style='width:100px;' src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['icon_url'] = "No Icon Found";
        });
        $venue_crud->setModel($venue_model,['venue_id','icon_url'],['venue','icon_url']);

    }
}
