<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_test extends Page {

    public $title='test';

    function init() {
        parent::init();

        // $this->add('View_LocationPicker');
        $form = $this->add('Form');
        $form->addField('text');
        $text_field = $form->getElement('text');
        // $form->addSubmit('submit');

        // $vp = $this->add('VirtualPage');
        // $this->js(true)->univ()->frameURL('MyPopup',$vp->getURL());
        // $vp->set(function($vp){
        //         $form = $vp->add('Form');
        //         $form->addField('RichText','text');
        // });
        // if($form->isSubmitted()){
        // 	throw new \Exception($form['text']);
        	
        // }

    }

    // function defaultTemplate(){
    // 	return ['test'];
    // }

}
