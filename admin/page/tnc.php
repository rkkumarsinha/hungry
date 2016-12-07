<?php

/**
 * Created by Rakesh Sinha
 * Date: 21.2.15
 * Time: 14:57
 */
class page_tnc extends page_adminconfiguration {

    public $title='TNC';

    function init() {
        parent::init();

        $form = $this->add('Form');
        $model = $this->add('Model_Configuration')->tryLoadAny(1);

        $form->setModel($model,['restaurant_tnc','event_tnc','destination_tnc']);
        $form->addSubmit('save');
        if($form->isSubmitted()){
            $form->save();
            $form->js()->univ()->successMessage('Saved')->execute();
        }
    }

}
