<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    $routefiles = ['student.php'];

    foreach ($routefiles as $routefile){

        $rt = require __DIR__ . '/routes/' . $routefile;
        $rt($app);

    }

};