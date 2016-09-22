<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_country extends Page {

    public $title='Country';

    function init() {
        parent::init();
      
        $this->add('CRUD')->setModel('Country');


    }

}
