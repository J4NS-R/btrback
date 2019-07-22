<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/environ.php';

session_start();

try {

    //Connect to db
    $db_options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO('mysql:host=localhost;dbname='.DB_NAME.';charset=utf8mb4', DB_USR, DB_PWD,
        $db_options);

    // Instantiate the app
    $settings = require __DIR__ . '/../src/settings.php';
    $app = new \Slim\App($settings);

    // Set up dependencies
    $dependencies = require __DIR__ . '/../src/dependencies.php';
    $dependencies($app);

    // Register routes
    $routes = require __DIR__ . '/../src/routes.php';
    $routes($app, $pdo);

    // Run app
    $app->run();


} catch (\Exception $e) {
    if (APP_IN_PROD)
        echo 'Internal server error. Tell someone.';
    else
        echo 'Error: ' . $e->getMessage();
}

