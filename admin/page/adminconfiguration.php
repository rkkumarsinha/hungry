<?php

class page_adminconfiguration extends Page {

    public $title='Configuration Management';

    function init() {
        parent::init();

        $this->api->menu->addItem(['Location','icon'=>'ajust'],'/location');
        $this->api->menu->addItem(['Configuration','icon'=>'cog'],'/configuration');
        $this->api->menu->addItem(['Cancle Reason','icon'=>'cog'],'/cancleregion');
		$this->api->menu->addItem(['Email Template','icon'=>'ajust'],'/emailtemplate');
		$this->api->menu->addItem(['TNC','icon'=>'ajust'],'/tnc');
    }
}
