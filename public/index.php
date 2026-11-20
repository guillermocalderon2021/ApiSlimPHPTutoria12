<?php
//VERSION FINAL PARA USAR 2026
//AUTOR: Ing. Alex Castillo.
//API REST EMPLEADOS
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

//MANEJO DE VARIABLES DE ENTORNO
use Dotenv\Dotenv;  
//Vistas con TWIG
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

//Se detecta automaticamente la carpeta base donde esta corriendo la API
//En XAMPP es necesario poner la ruta completa de la carpeta public, en este caso es /apislimphptutoria12/public y en Render normalmente será vacío.
//$app->setBasePath('/ApiSlimPHPTutoria12/public');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = str_replace('/index.php', '', $scriptName);
if ($basePath !== '') {
	$app->setBasePath($basePath);
}

// Usamos la libreria de vlucas/phpdotenv para recuperar las variables de entorno de .env en el directorio raiz
$envPath = __DIR__ . '/../';
if (file_exists($envPath . '.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->load();
}

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
