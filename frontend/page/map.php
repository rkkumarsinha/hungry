<?php
class page_map extends Page
{
    function init(){
        parent::init();

        $this->gallery_model = $this->add('Model_Image');
        
        $gallery = $this->add('View_Lister_RestaurantGallery',
        						['restaurant_id'=>97]
        					);
    	$gallery->setModel($this->gallery_model);

    }
}

