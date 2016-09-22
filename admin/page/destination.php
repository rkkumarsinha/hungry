<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination extends Page {

    public $title='Destination';

    function init() {
        parent::init();

        $c = $this->add('CRUD');
        $destination_model = $this->add('Model_Destination');

        $c->setModel(
        		$destination_model,
        		array(
                    'city_id',
        			'area_id',
        			'logo_image_id',
        			'banner_image_id',
        			'display_image_id',
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
        			'created_at',
        			'longitude',
        			'latitude',
        			'is_featured',
        			'is_popular',
        			'is_recommend',
        			'monday',
        			'tuesday',
        			'wednesday',
        			'thursday',
        			'friday',
        			'saturday',
        			'sunday',
        			'url_slug',
        			'food_type',
        			'payment_method',
        			'booking_policy',
        			'cancellation_policy',
                    'guidelines',
                    'how_to_reach'
        			),
        		array('name')
        	);

		$c->grid->add('VirtualPage')
            ->addColumn('Actions')
            ->set(function($page){
                $id = $_GET[$page->short_name.'_id'];
                
                $t = $page->add('Tabs');
                
                $space_tab = $t->addTabUrl($this->app->url('/destination/space',['destination_id'=>$id]),'Space');
                $package_tab = $t->addTabUrl($this->app->url('/destination/package',['destination_id'=>$id]),'Package');
                $facility_tab = $t->addTabUrl($this->app->url('/destination/facilityassociation',['destination_id'=>$id]),'Facility');
                $occasion_tab = $t->addTabUrl($this->app->url('/destination/occasionassociation',['destination_id'=>$id]),'Occassion');
                $service_tab = $t->addTabUrl($this->app->url('/destination/serviceassociation',['destination_id'=>$id]),'Service');
                $venue_tab = $t->addTabUrl($this->app->url('/destination/venueassociation',['destination_id'=>$id]),'Venue');
                $gallery_tab = $t->addTabUrl($this->app->url('/destination/gallery',['destination_id'=>$id]),'Gallery');
                $review_tab = $t->addTabUrl($this->app->url('/destination/review',['destination_id'=>$id]),'Review');
        });

    }

}
