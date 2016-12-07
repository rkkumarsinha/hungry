<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_cancleregion extends page_adminconfiguration{

    public $title='Cancle Region';

    function init() {
        parent::init();
      
        $this->add('CRUD')->setModel('CancledReason');


    }

}
