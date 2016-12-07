<?php

class page_location extends page_adminconfiguration{

    public $title='Location';

    function init() {
        parent::init();

        $tab = $this->add('Tabs');
        $tab->addTabURL('country','Country');
        $tab->addTabURL('state','State');
        $tab->addTabURL('city','City');
        $tab->addTabURL('area','Area');
    }
}
