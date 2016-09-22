<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_destination_highlight extends Page {

    public $title='Highlight';

    function init() {
        parent::init();

       	$tab = $this->add('Tabs');
       	// $tab->addTabUrl('/destination/space','Space');
       	// $tab->addTabUrl('/destination/package','Package');
       	$tab->addTabUrl('/destination/occasion','Occasion');
       	$tab->addTabUrl('/destination/facility','Facility');
       	$tab->addTabUrl('/destination/service','Service');

        
    }

}
