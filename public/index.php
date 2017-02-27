<?php
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';
$api = GreycatMedia\VmApi\Api::init(require_once 'config/config.php');
$api->run();
