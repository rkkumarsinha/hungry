<?php

class page_test extends Page{
    function init(){
        parent::init();

        $this->add('View')->set('Test');

        $user_model = $this->add('Model_User')->load(9);
        $outbox = $this->add('Model_Outbox');
        
        $body = file_get_contents("/var/www/html/hungrydunia/mail1.mail");
        $outbox->sendEmail("techrakesh91@gmail.com",$subject="Here is the subject",$body,$user_model);
        return;
        // $dc = $this->add('Model_DiscountCoupon')->load(36);
        
        // $dc->sendDiscount("rksinha.btech@gmail.com","9413752459");

        $model_restaurants = $this->add('Model_Restaurant');
        $restaurant_array = array('data'=>array());

        // array_merge($restaurant_array['data'],$data);
        $i = 0;
        foreach ($model_restaurants as $restaurant) {
            $restaurant_array['data'][$i] = array();
            $restaurant_array['data'][$i]['id'] = $restaurant['id'];
            $restaurant_array['data'][$i]['title'] = $restaurant['name'];
            $restaurant_array['data'][$i]['category'] = $restaurant['category'];
            $restaurant_array['data'][$i]['location'] = $restaurant['area'];
            $restaurant_array['data'][$i]['latitude'] = $restaurant['latitude']?:24.59196503014968;
            $restaurant_array['data'][$i]['longitude'] = $restaurant['longitude']?:73.68756600000006;
            $restaurant_array['data'][$i]['type_icon'] = $restaurant['category_icon_url'];
            $restaurant_array['data'][$i]['color'] = "#ff6600";
            // $restaurant_array['data'][$i]['featured'] = 1;
            $restaurant_array['data'][$i]['gallery'] =  $restaurant->getImages();
            $restaurant_array['data'][$i]['description'] =  $restaurant['about_restaurant'];
            // $restaurant_array['data'][$i]['features'] =  $restaurant->getFeatures();
                                                        // array(
                                                        //     0=>"assets/img/items/1.jpg",
                                                        //     1=>"assets/img/items/5.jpg"
                                                        // );

            $i++;    

        }

        echo "<pre>";
        print_r($restaurant_array);
    }


}