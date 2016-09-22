<?php

// include'../vendor/atk4/atk4/loader.php';
require_once '../vendor/autoload.php';
include 'lib/Api.php';

$api = new Api('apisrv');
$api->main();
