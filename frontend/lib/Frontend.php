<?php
class Frontend extends ApiFrontend {
    public $api_public_path;
    public $api_base_path;
    function init() {
        parent::init();

        $this->api_public_path = dirname(@$_SERVER['SCRIPT_FILENAME']);
        $this->api_base_path = dirname(dirname(@$_SERVER['SCRIPT_FILENAME']));

        // $this->add('Layout_Centered');
        $this->add('Layout_HungryDunia');

        $this->addLocations();
        $this->addProjectLocations();
        $this->addAddonsLocations();
        $this->add('jUI');
        $this->initAddons();

        $this->dbConnect();
        
        $auth=$this->add('Auth');
        $auth->usePasswordEncryption();
        $auth->setModel('User','email','password');

        //Subscription Form
        $f = $this->layout->add('Form',null,'subscription',['form\stacked'])->addClass('hungry-subscription');
        $f->addField('email')->validateNotNull()->validateField('filter_var($this->get(), FILTER_VALIDATE_EMAIL)');
        $f->addSubmit('Subscribe')->addClass('btn-block atk-swatch-orange');

        if($f->isSubmitted()){
            $subs = $this->add('Model_Subscriber');
            $subs['name'] = $f['email'];
            $subs->save();
            $f->js()->univ()->reload()->execute();
            $subs->send();
        }

        $this->api->today = date('Y-m-d');

        if($this->api->auth->model->id){
            $this->layout->add('View',null,'username')->setElement('strong')->set("Hello ".$this->api->auth->model['name'])->setStyle('color','white');
            $this->layout->template->tryDel('register_wrapper');
            $this->layout->template->tryDel('signlogout');


            if($this->app->auth->model['type']==="host"){
                $hostdropdown = $this->app->layout->add('View_HostDropdown',null,'hostdropdown');
            }else{
                $this->app->layout->add('View',null,'hostdropdown')->setElement('li')
                    ->setHtml('<a href="'.$this->api->url('account').'">Account</a>');
            }

            $this->app->layout->add('View',null,'hostdropdown')->setElement('li')
                    ->setHtml('<a href="'.$this->api->url('logout').'">Logout</a>')->addClass('logout');

        }else{
            $this->layout->add('View',null,'signlogout')
                ->setElement('a')
                ->setAttr('href', $this->api->url('signin'))
                ->addClass('signin')
                ->set('Sign In');
            $this->layout->template->tryDel('login_menu');
        }


        $this->app->city_name = $this->app->recall('city_id')?:$_GET['city']?:"Udaipur";
        // throw new \Exception($this->app->city_name);
        
        if($this->app->city_name){
            if(is_numeric($this->app->city_name)){
                $this->app->city = $city = $this->add('Model_City')->load($this->app->city_name);
                $this->app->city_id = $city->id;
                $this->app->city_name = $city['name'];
            }else{
                $this->app->city = $city = $this->add('Model_City')->loadBy('name',$this->app->city_name);
                $this->app->city_id = $city->id;
                $this->app->city_name = $city['name'];
                
                // $area = $this->add('Model_Area')->loadBy('name',$this->app->city_name);
                // $this->app->area_id = $area->id;
            }
        }

        $this->layout->add('View_Location',null,'location');
        // $this->js(true)->univ()->alert('hello');
        // $this->on('click','.host-list',function($js,$data){
        //     throw new \Exception("Error Processing Request", 1);
            
        //     return $js->alert("hello");
        // });
        
    }

    function addLocations() {        
        $this->api->pathfinder->base_location->defineContents(array(
            'docs'=>array('docs','doc'),   // Documentation (external)
            'content'=>'content',          // Content in MD format
            'addons'=>array('vendor','../addons','../shared'),
            'php'=>array('shared','vender'),
            'js'=>array('shared','vender','addons','vendor'),
            'cs'=>array('shared','vender','addons','vendor'),
        ))->setBasePath($this->api_base_path);
    }

    function addProjectLocations() {
       // $this->pathfinder->base_location->setBasePath($this->api_base_path);
       // $this->pathfinder->base_location->setBaseUrl($this->url('/'));
        $this->pathfinder->addLocation(
            array(
                'page'=>'page',
                'php'=>'../shared',
            )
        )->setBasePath($this->api_base_path);
        $this->pathfinder->addLocation(
            array(
                'js'=>'js',
                'css'=>'css',
            )
        )
                ->setBaseUrl($this->url('/'))
                ->setBasePath($this->api_public_path)
        ;
    }

    function addAddonsLocations() {
        $base_path = $this->pathfinder->base_location->getPath();
        $file = $base_path.'/sandbox_addons.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $objects = json_decode($json);
            foreach ($objects as $obj) {
                // Private location contains templates and php files YOU develop yourself
                /*$this->private_location = */
                $this->api->pathfinder->addLocation(array(
                    'docs'      => 'docs',
                    'php'       => 'lib',
                    'template'  => 'templates',
                ))
                        ->setBasePath($base_path.'/'.$obj->addon_full_path)
                ;

                $addon_public = $obj->addon_symlink_name;
                // this public location cotains YOUR js, css and images, but not templates
                /*$this->public_location = */
                $this->api->pathfinder->addLocation(array(
                    'js'     => 'js',
                    'css'    => 'css',
                    'public' => './',
                    //'public'=>'.',  // use with < ?public? > tag in your template
                ))
                        ->setBasePath($this->api_base_path.'/'.$obj->addon_public_symlink)
                        ->setBaseURL($this->api->url('/').$addon_public) // $this->api->pm->base_path
                ;
            }
        }
    }
    function initAddons() {
        $base_path = $this->pathfinder->base_location->getPath();
        $file = $base_path.'/sandbox_addons.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $objects = json_decode($json);
            foreach ($objects as $obj) {
                // init addon
                $init_class_path = $base_path.'/'.$obj->addon_full_path.'/lib/Initiator.php';
                if (file_exists($init_class_path)) {
                    $class_name = str_replace('/','\\',$obj->name.'\\Initiator');
                    $init = $this->add($class_name,array(
                        'addon_obj' => $obj,
                    ));
                }
            }
        }
    }

    function initLayout(){

//        $l = $this->add('Layout_Fluid');

//        $m = $l->addMenu('MainMenu');
//        $m->addClass('atk-wrapper');
//        $m->addMenuItem('index','Home');
//        $m->addMenuItem('services','Services');
//        $m->addMenuItem('team','Team');
//        $m->addMenuItem('portfolio','Portfolio');
//        $m->addMenuItem('contact','Contact');
//
//        $l->addFooter()->addClass('atk-swatch-seaweed atk-section-small')->setHTML('
//            <div class="row atk-wrapper">
//                <div class="col span_4">
//                    © 1998 - 2013 Agile55 Limited
//                </div>
//                <div class="col span_4 atk-align-center">
//                    <img src="'.$this->pm->base_path.'images/powered_by_agile.png" alt="powered_by_agile">
//                </div>
//                <div class="col span_4 atk-align-right">
//                    <a href="http://colubris.agiletech.ie/">
//                        <span class="icon-key-1"></span> Client Login
//                    </a>
//                </div>
//            </div>
//        ');

        parent::initLayout();
    }
}





//        $publicDir = dirname(@$_SERVER['SCRIPT_FILENAME']);
//        $baseDir   = dirname($publicDir);
//
//
//        //$parent_directory=/*dirname(*/dirname(@$_SERVER['SCRIPT_FILENAME'])/*)*/;
//        var_dump($baseDir);
//
//        $this->pathfinder->public_location->addRelativeLocation('public/',
//            array(
//                'css'=>'css',
//                'public'=>'.',
//            )
//        );
//