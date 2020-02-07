<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use ExampleApp\HelloWorld;
use FastRoute\RouteCollector;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Narrowspark\HttpEmitter\SapiEmitter;
use Relay\Relay;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use function DI\create;
use function DI\get;
use function FastRoute\simpleDispatcher;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// DI (dependency injection)
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions([
    HelloWorld::class => create(HelloWorld::class)->constructor(get('Foo'), get('Response')),
    'Foo' => 'bar',
    'Response' => function() {
        return new Response();
    }
]);

$container = $containerBuilder->build();

// Defining routes
$routes = simpleDispatcher(function(RouteCollector $r) {
    $r->get('/hello', HelloWorld::class);
});

// Set up middleware
$middlewareQueue[] = new FastRoute($routes);
$middlewareQueue[] = new RequestHandler($container);

// Request handlers
$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle(ServerRequestFactory::fromGlobals());
$emitter = new SapiEmitter();
return $emitter->emit($response);
