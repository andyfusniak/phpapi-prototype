<?php
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';
$api = GreycatMedia\Api\Api::init(require_once 'config/config.php');
$api->run();
