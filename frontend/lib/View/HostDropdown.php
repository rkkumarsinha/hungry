<?php

class View_HostDropdown extends View{
    public $first_list_id;
    public $first_list_type;
	function init(){
		parent::init();

        $all_listing = $this->api->auth->model->getAllListing();

        $count=1;
        foreach ($all_listing as $key => $value) {

            $data = explode("-", $key);
            $this->add('View')->setElement('li')
                    ->setAttr('data-listid',$data[0])
                    ->setAttr('data-listtype',$data[1])
                    ->addClass('host-list')
                    ->setHtml('<a href="#">'.$value.'</a>');

            if($count===1){
                $this->first_list_id = $data[0];
                $this->first_list_type = $data[1];
            }
        }

        $url = $this->app->url('account');
        $this->on('click','.host-list',function($js,$data)use($url){
            $this->app->memorize('HOSTLISTID',$data['listid']);
            $this->app->memorize('HOSTLISTTYPE',$data['listtype']);
            return $js->univ()->redirect($url);
        });
	}
}