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
        // $form = $this->add('Form');
        // $form->addField('text');
        // $text_field = $form->getElement('text');
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

        $grid = $this->add('Grid');
        $model = $this->add('Model_Restaurant');
        $current_lat = 24.586885;
        $current_long = 73.69917499999997;
        // $q = $model->dsql();
        // $latlng = $q->expr('ABS(ABS([0] - [1]) + ABS([2] - [3]))',[$model->getElement('latitude'),$current_lat,$model->getElement('longitude'),$current_long]);
        $model->addExpression('distance')->set(function($m,$q)use($current_lat,$current_long){
            return $q->expr('ABS(ABS([0] - [1]) + ABS([2] - [3]))',[$m->getElement('latitude'),$current_lat,$m->getElement('longitude'),$current_long]);
        });
        $model->addCondition('distance',"<=",10);
        $model->setOrder('distance','asc');

        $grid->setModel($model,['name','distance']);
        $grid->addPaginator($ipp=15);

    }

    // function defaultTemplate(){
    // 	return ['test'];
    // }

}
