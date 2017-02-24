<?php
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\App;
use Slim\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Gm\Mapper\ProfileMapper;
use Gm\Mapper\Exception\ProfileNotFoundException;

$config = require_once('config/config.php');

$container = new Container($config['slim'] ?? null);
$container['logger'] = function($container) use ($config) {
    $logger = new Logger(
        $config['logger']['logger_name'] ?? 'phpapi'
    );

    $logger->pushHandler(
        new StreamHandler(
            $config['logger']['log_filepath'] ?? 'var/log',
            $config['logger']['log_level'] ?? Logger::ERROR
        )
    );

    return $logger;
};

$container['pdo'] = function($container) use ($config) {
    try {
        $logger = $container->get('logger');

        $dsn = 'mysql:host=' . $config['db']['dbhost']
             . ';dbname=' . $config['db']['dbname'];
        $user = $config['db']['dbuser'];
        $logger->debug(sprintf(
            'Data Source Name=%s, user=%s',
            $dsn,
            $user
        ));

        $pdo = new \PDO(
            $dsn,
            $user,
            $config['db']['dbpass'],
            [
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]
        );

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (\PDOException $e) {
        throw $e;
    } catch (\Exception $e) {
        throw $e;
    }
};

$container['profileMapper'] = function($container) {
    $profileMapper = new ProfileMapper(
        $container->get('pdo')
    );

    return $profileMapper->setLogger(
        $container->get('logger')
    );
};

$app = new App($container);
$app->get(
    '/profile/{profile_id}',
    function (Request $request, Response $response, $args) {
        $profileId = $request->getAttribute('profile_id');
        $profileMapper = $this->get('profileMapper');

        try {
            $item = $profileMapper->fetchProfileById($profileId);

            if (empty($item)) {
                return $response->withStatus(404)->write(sprintf(
                    '{"status":404,"message":"Profile %s not found"}',
                    $profileId
                ));
            }
        } catch (ProfileNotFoundException $e) {
            return $response->withStatus(404)->write(
                '{"message": "Profile not found"}'
            );
        }

        return $response->withStatus(200)->write(
            json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
);

$app->run();
