<?php

/**
 * Created by R
 * Date: 21.2.15
 * Time: 14:57
 */
class page_restaurant extends Page {

    public $title='Restaurant';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $rst_model = $this->add('Model_Restaurant');

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
                        ['country_id','state_id','city_id','area_id','discount_id','logo_image_id','banner_image_id','display_image_id','name','owner_name','about_restaurant','address','mobile_no','phone_no','email','website','facebook_page_url','instagram_page_url','rating','avg_cost_per_person_veg','avg_cost_per_person_nonveg','avg_cost_per_person_thali','avg_cost_of_a_beer','credit_card_accepted','reservation_needed','monday','tuesday','wednesday','thursday','friday','saturday','sunday','url_slug','discount','discount_subtract','is_featured','is_popular','is_recommend','latitude','longitude','food_type'],
                        ['name','address']
                    );
        $c->grid->addQuickSearch(['name']);

        // if($c->isEditing()){
        //     $temp = $c->form->getElement('discount_id')->getModel();
        //     $temp->addCondition('is_discount',true);
        // }

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

    }

}
