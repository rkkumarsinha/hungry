<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_verifydetail extends Page {

    public $title='Listing Verification';

    function init() {
        parent::init();

        $this->api->stickyGET('type');
        $this->api->stickyGET('id');

        $id = $_GET['id'];
        $host_business_type = $_GET['type'];

        $search_btn = $this->add('Button')->set('Show/Hide search form')->addClass('atk-swatch-yellow');

        $rest_to_be_verified = $this->add('Model_Restaurant')
                    ->addCondition('status','deactive')
                    ->addCondition('is_verified',false)
                    ;       
        $search_box = $this->add('View')->addClass('atk-box')->setStyle('display','none');
        $search_form = $search_box->add('Form');
        $rest_field = $search_form->addField('DropDown','restaurant_to_be_verify')->validateNotNull();
        $rest_field->setModel($rest_to_be_verified);
        $rest_field->setEmptyText('Please Select Restaurant To Be Verify');
        $search_form->addSubmit('Go');

        $search_btn->js('click',$search_box->js()->toggle());

        $detail_view = $this->add('View');


        if($search_form->isSubmitted()){
            $detail_view->js()->univ()->reload(['id'=>$search_form['restaurant_to_be_verify']])->execute();
        }

        // switch ($_GET['type']) {
        //     case 'restaurant':       
        //     break;
        //     case 'destination':
        //         $listing_model = $this->add('Model_Destination')->load($id);
        //     break;
        //     case 'event':
        //         $listing_model = $this->add('Model_Event')->load($id);
        //     break;
        // }

        $listing_model = $this->add('Model_Restaurant')->load($id);

        $this->title = 'Restaurant Verification of "'.$listing_model['name'].'" ';

        $host_restaurant = $listing_model;
        $tab = $detail_view->add('Tabs');
        $basic_info_tab = $tab->addTab('Basic Info');
        $image_gallery_tab = $tab->addTab('Image Gallery');
        $menu_tab = $tab->addTab('Menu');
        $highlight_tab = $tab->addTab('Highlight');
        $cuisine_tab = $tab->addTab('Cuisine');
        $category_tab = $tab->addTab('Category');


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
                   $verification_form->js()->univ()->errorMessage('restaurant verify but welcome email not send')->execute();
                }                
            }else{
                $verification_form->js()->univ()->errorMessage('restaurant verify but welcome email not send, because host email not found')->execute();
            }

            $verification_form->js()->univ()->successMessage('Restaurant Verify Successfully and welcome mail send')->execute();
        }

        $email_tab->add('View')->setHtml($body);

        // Basic Forms
        $basic_form = $basic_info_tab->add('Form');
        $basic_form->setModel($host_restaurant,
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
                                'about_restaurant',
                                'address',
                                'mobile_no',
                                'phone_no',
                                'email',
                                'website',
                                'facebook_page_url',
                                'instagram_page_url',
                                'rating',
                                'avg_cost_per_person_veg',
                                'avg_cost_per_person_nonveg',
                                'avg_cost_per_person_thali',
                                'avg_cost_of_a_beer',
                                'credit_card_accepted',
                                'reservation_needed',
                                'type',
                                'longitude',
                                'latitude',
                                'monday',
                                'tuesday',
                                'wednesday',
                                'thursday',
                                'friday',
                                'saturday',
                                'sunday',
                                'food_type'
                            ]);

        $basic_form->addSubmit("Update");
        if($basic_form->isSubmitted()){
            $basic_form->save();
            $basic_form->js()->univ()->successMessage("Updated Successfully");
        }

        // Restaurant Image Gallery
        $crud = $image_gallery_tab->add('CRUD');
        $rest_image = $this->add('Model_RestaurantImage')
                        ->addCondition('restaurant_id',$host_restaurant->id)
                        ->addCondition('type','restaurant')
                        ;

        $crud->setModel($rest_image,['image_id','image','status'],['image','status','created_at','approved_date']);

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

        // Menu image
        $menu_crud = $menu_tab->add('CRUD');
        $menu_image = $this->add('Model_RestaurantMenu')
                        ->addCondition('restaurant_id',$host_restaurant->id)
                        ->addCondition('type','menu')
                        ;

        $menu_crud->setModel($menu_image,['image_id','image','status'],['image','status','created_at','approved_date']);
        $menu_crud->grid->addPaginator(10);
        $menu_crud->grid->addHook('formatRow',function($g){
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

        // Restaurant Highlights
        $r_hl = $highlight_tab->add('Model_Restaurant_Highlight')->addCondition('restaurant_id',$host_restaurant->id);
        $hl_crud = $highlight_tab->add('CRUD');
        $hl_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
        $hl_crud->setModel($r_hl);

        // Restaurant Keywords
        $cu_hl = $cuisine_tab->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$host_restaurant->id);
        $cu_crud = $cuisine_tab->add('CRUD');
        $cu_crud->setModel($cu_hl);
        $cu_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });

        // Restaurant Images
        $cat_crud = $category_tab->add('CRUD');
        $cat_asso = $this->add('Model_CategoryAssociation');
        $cat_asso->addCondition('restaurant_id',$host_restaurant->id);
        $cat_crud->setModel($cat_asso);
        $cat_crud->grid->addHook('formatRow',function($g){
            if($g->model['icon_url'])
                $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
            else
                $g->current_row_html['image'] = "No Icon Found";
        });
    

    }
}
