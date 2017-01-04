<?php

/**
 * Created by R
 * Date: 21.2.15
 * Time: 14:57
 */
class page_restaurant extends page_adminrestaurant {

    public $title='Restaurant';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $rst_model = $this->add('Model_Restaurant');
        $rst_model->addExpression('user_status')->set(function($m,$q){
            return $m->refSQL('user_id')->fieldQuery('is_verified');
        });

        $f = $c->grid->add('Form',null,'grid_buttons',['form/rempty'])->addClass('atk-col-6 atk-align-right')->setStyle('margin-top','10px');
        $f->addField('DropDown','country')->setEmptyText('Select Country')->setModel('Country')->set($_GET['country']);
        $f->addField('DropDown','state')->setEmptyText('Select State')->setModel('State')->set($_GET['state']);
        $f->addField('DropDown','city')->setEmptyText('Select City')->setModel('City')->set($_GET['city']);
        $f->addField('DropDown','area')->setEmptyText('Select Area')->setModel('Area')->set($_GET['area']);
        $f->addSubmit('search')->addClass('atk-swatch-green');

         $f->onSubmit(function($f)use($c){
            return $c->js()->reload(
                                [
                                    'country'=>$f['country'],
                                    'state'=>$f['state'],
                                    'city'=>$f['city'],
                                    'area'=>$f['area']
                                ]);        
        });

        if($_GET['country']){
            $rst_model->addCondition('country_id',$_GET['country']);
        }
        if($_GET['state']){
            $rst_model->addCondition('state_id',$_GET['state']);
        }
        if($_GET['city']){
            $rst_model->addCondition('city_id',$_GET['city']);
        }
        if($_GET['area']){
            $rst_model->addCondition('area_id',$_GET['area']);
        }

        $c->setModel($rst_model,
                        ['user_id','country_id','state_id','city_id','area_id','discount_id','logo_image_id','banner_image_id','display_image_id','name','owner_name','about_restaurant','address','mobile_no','phone_no','email','website','facebook_page_url','instagram_page_url','rating','avg_cost_per_person_veg','avg_cost_per_person_nonveg','avg_cost_per_person_thali','avg_cost_of_a_beer','credit_card_accepted','reservation_needed','monday','tuesday','wednesday','thursday','friday','saturday','sunday','url_slug','discount','discount_subtract','is_featured','is_popular','is_recommend','latitude','longitude','food_type','is_verified','status','disclaimer','title','keyword','description','image_title','image_alt_text'],
                        ['user','name','address','user_status','status']
                    );
        $c->grid->addQuickSearch(['name']);

        $c->grid->addHook('formatRow',function($g){
            $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('verify_rest',['id'=>$g->model['id'],'type'=>'restaurant']).'">'.$g->model['name'].'</a>';
            // $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('restaurantdetail',['rest_id'=>$g->model['id']]).'">'.$g->model['name'].'</a>';
            $g->current_row_html['user_status'] = $g->model['user_status']?'<div class="atk-swatch-green" style="padding:2px;text-align:center;">verified</div>':'<div class="atk-swatch-red" style="padding:2px;text-align:center;">to be verified</div>';
        });
        // if($c->isEditing()){
        //     $temp = $c->form->getElement('discount_id')->getModel();
        //     $temp->addCondition('is_discount',true);
        // }

        $c->grid->add('VirtualPage')
            ->addColumn('send_email_verification')
            ->set(function($page){
                $id = $_GET[$page->short_name.'_id'];

                $business_model = $rest = $this->add('Model_Restaurant')->load($id);

                if(!$rest['user_id']){
                    $page->add('View_Error')->set('Host not found');
                    return;
                }

                $user = $page->add('Model_User')->tryLoad($rest['user_id']);
                if(!$user->loaded()){
                    $page->add('View_Error')->set('Host not found');
                    return;
                }

                if($user['type'] != "host"){
                    $page->add('View_Error')->set('Host not found, user is not host type '.$user->id);
                    return;   
                }
                
                if($user['is_verified']){
                    $page->add('View_Info')->set('Host is verified, do you want to send email again'.$user['is_verified']);
                }
                    // $page->add('View_Info')->set('Host is verified, do you want to send email again');

                $email_template = $page->add('Model_EmailTemplate')
                                ->addCondition('name',"EMAILVERIFICATIONHOST")
                                ->tryLoadAny();
                $subject = $email_template['subject'];
                $body = $email_template['body'];

                $body = str_replace("{user_name}", $user['name'], $body);
                $body = str_replace("{business_name}", $business_model['name'], $body);
                $body = str_replace("{verification_email_link}", $user->getVerificationURL()."&business=".$business_model->id."&business_type=restaurant", $body);

                $form = $page->add('Form');
                $form->add('View')->setHtml($body);
                $form->addSubmit('Send Verification');

                if($form->isSubmitted()){
                    $outbox = $this->add('Model_Outbox');
                    try{
                        $email_response = $outbox->sendEmail($user['email'],$subject,$body,$user);
                        $outbox->createNew("Verification Email From Admin",$user['email'],$subject,$body,"Email","New Host User Registration with ".$business_name['name'],$user->id,$user_model);
                    }catch(Exception $e){
                        // $form->js()->univ()->errorMessage('email not send')->execute();
                    }
                    $form->js()->univ()->successMessage('email send successfully')->execute();
                }             
        });

        $c->grid->add('VirtualPage')
            ->addColumn('Actions')
            ->set(function($page){
                $id = $_GET[$page->short_name.'_id'];
                
                $t = $page->add('Tabs');
                
                //Image Tab
                $image_tab = $t->addTab('Image Gallery');
                $crud = $image_tab->add('CRUD');
                $rest_image = $this->add('Model_RestaurantImage')->addCondition('restaurant_id',$id);
                $crud->setModel($rest_image);
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


                $menu_tab = $t->addTab('Menu');
                $crud_menu = $menu_tab->add('CRUD');
                $rest_menu = $this->add('Model_RestaurantMenu')->addCondition('restaurant_id',$id);
                $crud_menu->setModel($rest_menu);
                $crud_menu->grid->addPaginator(10);

                $tag_tab = $t->addTab('Tags');
                // $discount_tab = $t->addTab('Discount');
                // $discount_crud = $discount_tab->add('CRUD');
                // $discount_model = $this->add('Model_RestaurantOffer')->addCondition('restaurant_id',$id);
                // $discount_crud->setModel($discount_model);
                // if($discount_crud->isEditing()){
                //     $temp_model = $discount_crud->form->getElement('offer_and_discount_id')->getModel();
                //     $temp_model->addCondition('is_discount',true);
                // }


                $r_hl = $this->add('Model_Restaurant_Highlight')->addCondition('restaurant_id',$id);
                $highlight_tab = $t->addTab('Highlight');
                $hl_crud = $highlight_tab->add('CRUD');

                $hl_crud->grid->addHook('formatRow',function($g){
                    if($g->model['icon_url'])
                        $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
                    else
                        $g->current_row_html['image'] = "No Icon Found";
                });
                $hl_crud->setModel($r_hl);

                $cuisine_tab = $t->addTab('Cuisine');
                $cu_crud = $cuisine_tab->add('CRUD');
                $cu_hl = $this->add('Model_Restaurant_Keyword')->addCondition('restaurant_id',$id);
                $cu_crud->setModel($cu_hl);
                $cu_crud->grid->addHook('formatRow',function($g){
                    if($g->model['icon_url'])
                        $g->current_row_html['icon_url'] = "<img src=".$g->model['icon_url'].">";
                    else
                        $g->current_row_html['image'] = "No Icon Found";
                });
                $cu_crud->setModel($r_hl);

                $offers_tab = $t->addTab('Offers');
                $offers_crud = $offers_tab->add('CRUD');
                $offers_model = $this->add('Model_RestaurantOffer')->addCondition('restaurant_id',$id);
                $offers_crud->setModel($offers_model);

                $category_tab = $t->addTab('Category');
                $cat_crud = $category_tab->add('CRUD');
                $cat_asso = $this->add('Model_CategoryAssociation');
                $cat_asso->addCondition('restaurant_id',$id);
                $cat_crud->setModel($cat_asso);

        });

        $c->add("misc/Export");
    }
}
