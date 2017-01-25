<?php
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

use Slim\App;
use Slim\Container;
use Aws\Sdk;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Gm\Mapper\ProfileMapper;
use Gm\Mapper\Exception\ProfileNotFoundException;


$container = new Container();
$container['dynamoDbClient'] = function ($container) {
    $sdk = new Aws\Sdk([
        'region'   => 'eu-west-2',
        'version'  => 'latest',
        'credentials' => [
            'key'    => 'AKIAIBMCHCHXNZ6GNZ3A',
            'secret' => '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'
        ]
    ]);
    return $sdk->createDynamoDb();
};
$container['profile_mapper'] = function($container) {
    return new ProfileMapper(
        $container->get('dynamoDbClient')
    );
};


$app = new App($container);

$app->get('/profile/{profile_id}', function (Request $request, Response $response, $args) {
    $profileId = $request->getAttribute('profile_id');
    $profileMapper = $this->get('profile_mapper');

    try {
        $item = $profileMapper->fetchProfileById($profileId);
    } catch (ProfileNotFoundException $e) {
        return $response->withStatus(404)->write('{"message": "Profile not found"}');
    }

    return $response->withStatus(200)->write(json_encode($item));
});

$app->run();
