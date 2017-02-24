<?php
use Monolog\Logger;

return [
    'slim' => [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ],
    'logger' => [
        'logger_name' => 'phpapi',
        'log_filepath' => 'var/log',
        'log_level' => Logger::DEBUG
    ],
    'db' => [
        'dbhost' => 'localhost',
        'dbuser' => 'root',
        'dbpass' => 'mysql',
        'dbname' => 'phpapi'
    ]
];
