<?php
/**
 * App_Admin should be used for building your own application's administration
 * model. The benefit is that you'll have access to a number of add-ons which
 * are specifically written for admin system.
 *
 * Exporting add-ons, database migration, test-suites and other add-ons
 * have developed User Interface which can be simply "attached" to your
 * application's admin.
 *
 * This is done through hooks in the Admin Class. It's also important that
 * App_Admin relies on layout_fluid which makes it easier for add-ons to
 * add menu items, sidebars and foot-bars.
 */
class App_Admin extends App_Frontend {

    public $title='Agile Toolkit™ Admin';

    private $controller_install_addon;

    public $layout_class='Layout_Fluid';

    public $auth_config=array('admin'=>'admin');

    /** Array with all addon initiators, introduced in 4.3 */
    private $addons=array();

    function init() {
        parent::init();
        $this->add($this->layout_class);

        $this->menu = $this->layout->addMenu('Menu_Vertical');
        $this->menu->swatch='ink';

        $this->add('jUI');
    }

    function initLayout() {
        $this->addLayout('mainMenu');

        parent::initLayout();

        $this->initTopMenu();
    }

    function initTopMenu() {
        $m=$this->layout->add('Menu_Horizontal',null,'Top_Menu');
        //$m->addClass('atk-size-kilo');
        $m->addItem('Admin','/');
        // $m->addItem('AgileToolkit','/sandbox/dashboard');
        // $m->addItem('Documentation','http://book.agiletoolkit.org/');
    }

}
