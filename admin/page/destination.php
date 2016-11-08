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
        $destination_model->addExpression('user_status')->set(function($m,$q){
            return $m->refSQL('user_id')->fieldQuery('is_verified');
        });

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
                    'how_to_reach',
                    'disclaimer',
                    'status',
                    'is_verified'
        			),
        		array('name','user','user_status')
        	);
        
        $c->grid->addHook('formatRow',function($g){
            $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('verify_event',['id'=>$g->model['id'],'type'=>'event']).'">'.$g->model['name'].'</a>';
            // $g->current_row_html['name'] = '<a style="width:100px;" target="_blank" href="'.$this->api->url('restaurantdetail',['rest_id'=>$g->model['id']]).'">'.$g->model['name'].'</a>';
            $g->current_row_html['user_status'] = $g->model['user_status']?'<div class="atk-swatch-green" style="padding:2px;text-align:center;">verified</div>':'<div class="atk-swatch-red" style="padding:2px;text-align:center;">to be verified</div>';
        });

        $c->grid->add('VirtualPage')
            ->addColumn('send_email_verification')
            ->set(function($page){
                $id = $_GET[$page->short_name.'_id'];

                $business_model = $destination = $this->add('Model_Destination')->load($id);

                if(!$destination['user_id']){
                    $page->add('View_Error')->set('Host not found');
                    return;
                }

                $user = $page->add('Model_User')->tryLoad($destination['user_id']);
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

                $email_template = $page->add('Model_EmailTemplate')
                                ->addCondition('name',"EMAILVERIFICATIONHOST")
                                ->tryLoadAny();
                $subject = $email_template['subject'];
                $body = $email_template['body'];

                $body = str_replace("{user_name}", $user['name'], $body);
                $body = str_replace("{business_name}", $business_model['name'], $body);
                $body = str_replace("{verification_email_link}", $user->getVerificationURL()."&business=".$business_model->id."&business_type=destination", $body);

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
