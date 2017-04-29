<?php declare(strict_types=1);
namespace GreycatMedia\VmApi;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\App;
use Slim\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GreycatMedia\VmApi\Mapper\ProfileMapper;
use GreycatMedia\VmApi\Mapper\Exception\ProfileNotFoundException;
use GreycatMedia\VmApi\Middleware\AuthenticationMiddleware;

class Api
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Slim\App;
     */
    protected $app;

    /**
     * @var Slim\Container
     */
    protected $container;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function init(array $config)
    {
        $api = new Api($config);
        $api->setupContainer();
        $api->setupSlimApp();
        $api->setupRoutes();
        return $api;
    }

    private function setupContainer()
    {
        $config = $this->config;

        $container = new Container(
            $this->config['slim'] ?? null
        );

        $container['logger'] = function ($container) use ($config) {
            $logger = new Logger(
                $config['logger']['logger_name'] ?? 'api'
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
        $this->container = $container;
    }

    private function setupSlimApp()
    {
        $this->app = new App($this->container);

        // hmac authentication
        $this->app->add(new AuthenticationMiddleware());
    }

    private function setupRoutes()
    {
        $this->app->get(
            '/profile/{profile_id}',
            function (Request $request, Response $response, $args) {
                $profileId = (int) $request->getAttribute('profile_id');
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
    }

    public function run()
    {
        $this->app->run();
    }
}
