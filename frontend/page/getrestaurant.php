<?php
class page_getrestaurant extends Page{

    function init(){
        parent::init();
        
                
        // $data = array('0'=>array(
        //         "id"=> 1,
        //         "category"=> "real_estate",
        //         "title"=> "Steak House Restaurant",
        //         "location"=> "63 Birch Street",
        //         "latitude"=> 51.541599,
        //         "longitude"=> -0.112588,
        //         "url"=> "item-detail.html",
        //         "type"=> "Apartment",
        //         "type_icon"=> "assets/icons/store/apparel/umbrella-2.png",
        //         "rating"=> 4,
        //         "gallery"=>
        //             [
        //                 "assets/img/items/1.jpg",
        //                 "assets/img/items/5.jpg",
        //                 "assets/img/items/4.jpg"
        //             ],
        //         "features"=>
        //             [
        //                 "Free Parking",
        //                 "Cards Accepted",
        //                 "Wi-Fi",
        //                 "Air Condition",
        //                 "Reservations",
        //                 "Teambuildings",
        //                 "Places to seat"
        //             ],
        //         "date_created"=> "2014-11-03",
        //         "price"=> "$2500",
        //         "featured"=> 0,
        //         "color"=> "",
        //         "person_id"=> 1,
        //         "year"=> 1980,
        //         "special_offer"=> 0,
        //         "item_specific"=>
        //             {
        //                 "bedrooms"=> 2,
        //                 "bathrooms"=> 2,
        //                 "rooms"=> 4,
        //                 "garages"=> 1,
        //                 "area"=> 240
        //             },
        //         "description"=> "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet pellentesque mauris. Proin sit amet scelerisque risus. Donec semper semper erat ut mollis. Curabitur suscipit, justo eu dignissim lacinia, ante sapien pharetra duin consectetur eros augue sed ex. Donec a odio rutrum, hendrerit sapien vitae, euismod arcu.",
        //         "last_review"=> "Curabitur odio nibh, luctus non pulvinar a, ultricies ac diam. Donec neque massa, viverra interdum eros ut, imperdiet",
        //         "last_review_rating"=> 5
        //     }
        //     ));

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
            $restaurant_array['data'][$i]['color'] = "#FF6600";
            $restaurant_array['data'][$i]['type'] = "Rakesh";
            $restaurant_array['data'][$i]['gallery'] =  $restaurant->getImages();
            $restaurant_array['data'][$i]['description'] =  $restaurant['about_restaurant'];
            $restaurant_array['data'][$i]['features'] =  array(0=>"Free Parking");
            $restaurant_array['data'][$i]['date_created'] =  $restaurant['created_at'];
            $restaurant_array['data'][$i]['special_offer'] =  "Todo special_offer";
            $restaurant_array['data'][$i]['item_specific'] = array(
                                                                    0=>array("bedrooms"=>2),
                                                                    1=>array("bathrooms"=>2),
                                                                    2=>array("rooms"=>4),
                                                                    3=>array("garages"=>1),
                                                                    4=>array("area"=>240)
                                                                    );

            $i++;    

        }



        // echo "<pre>";

        //     print_r($data);

        //     print_r($restaurant_array);

        // echo "</pre>";

        // exit;

        $data = json_encode($restaurant_array,true);

        echo $data;

        // echo "<pre>";

        // print_r($data);
        exit;
    }
}

