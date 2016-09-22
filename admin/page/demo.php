<?php

class page_demo extends Page{

	 public $title='Demo';

	function init(){
		parent::init();

		$c = $this->add('Columns');
		$c1 = $c->addColumn(4);
		$c2 = $c->addColumn(4);
		$c3 = $c->addColumn(4);

		$c1->add('View_Box')->set('Left')->addClass('atk-swatch-yellow');
		$c2->add('View_Info')->set('Center');
		$c3->add('View_Success')->set('Right');

		// $this->add('Button')->js('click')->univ()->alert('hello');
	}
}