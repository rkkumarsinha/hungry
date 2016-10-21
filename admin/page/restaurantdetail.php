<?php

/**
 * Created by R
 * Date: 21.2.15
 * Time: 14:57
 */
class page_restaurantdetail extends Page {

    public $title='Restaurant';

    function init() {
        parent::init();

        $id = $this->api->stickyGET('rest_id');

        $rest_model = $this->add('Model_Restaurant')->load($id);
        $t = $this->add('Tabs');

        $this->title = $rest_model['name'];
        //Basic
        $basic_tab = $t->addTab('Basic');
        $form = $basic_tab->add('Form');
        $form->setModel($rest_model);
        if($form->isSubmitted()){
            $form->save();
            $form->js()->univ()->successMessage("Saved Successfully")->execute();
        }

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

        // $tag_tab = $t->addTab('Tags');
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
    }

}
