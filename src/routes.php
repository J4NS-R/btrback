<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/utils.php';

return function (App $app, PDO $pdo) {

    $routefiles = ['student.php', 'application.php'];

    foreach ($routefiles as $routefile){

        $rt = require __DIR__ . '/routes/' . $routefile;
        $rt($app, $pdo);

    }

};
