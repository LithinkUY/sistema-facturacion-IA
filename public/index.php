<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Suppress deprecated warnings from Symfony 6.x in PHP 8.0
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

// Importante: Setear el facade root antes de manejar requests
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

// Registrar manualmente el binding de 'view' en el contenedor
// Esto previene ReflectionException cuando el Handler de excepciones intenta usar la clase view
if (!$app->has('view')) {
    $app->singleton('view', function($app) {
        // Crear un objeto minimalista que implemente los métodos necesarios
        return new class {
            public function exists($view) { return false; }
            public function file($path, $data = [], $mergeData = []) { return ''; }
            public function make($view, $data = [], $mergeData = []) { return ''; }
            public function replaceNamespace($namespace, $path) { return $this; }
        };
    });
}

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
