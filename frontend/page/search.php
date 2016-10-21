<?php
class page_search extends Page{

	function init(){
		parent::init();	

        $this->app->jui->addStaticInclude('http://maps.google.com/maps/api/js?sensor=true&language=en&key='.$this->api->getConfig('Google/MapKey'));
        $map = $this->add('View_FilterMapSearch');

        // $form = $map->add('Form',null,'filter');

        // $highlight = $map->add('CompleteLister',null,'highlight_lister',['view/filtermapsearch','highlight_lister']);
        // $highlight->setModel($this->add('Model_Highlight')->addCondition('is_active',true));

        // $keyword = $map->add('CompleteLister',null,'keyword_lister',['view/filtermapsearch','keyword_lister']);
        // $keyword->setModel($this->add('Model_Keyword'));
        // $area_field = $form->addField('DropDown','area')->setValueList([1=>'area1',2=>'area2']);
        // $area_field->js('change',$map->js()->reload(['area'=>2]));


	}
}

