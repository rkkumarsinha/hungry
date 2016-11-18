<?php

// include'../vendor/atk4/atk4/loader.php';
require_once '../vendor/autoload.php';
include 'lib/Api.php';

date_default_timezone_set("Asia/Calcutta");

$api = new Api('apisrv');
$api->main();

