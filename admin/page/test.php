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

        $form = $this->add('Form');
        $form->addField('RichText','text');
        $form->addSubmit('submit');

        if($form->isSubmitted()){
        	throw new \Exception($form['text']);
        	
        }

    }

    // function defaultTemplate(){
    // 	return ['test'];
    // }

}
