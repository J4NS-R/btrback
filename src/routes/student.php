<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app, PDO $pdo) {
    $container = $app->getContainer();

    $app->get('/all_majors', function (Request $request, Response $response, array $args) use ($pdo) {

        $val = validate_request($request);
        if ($val['success']){

            $stmt = $pdo->prepare('SELECT DISTINCT major_name FROM btr_major');
            $stmt->execute();

            $majors = [];
            while($maj = $stmt->fetch()){
                array_push($majors, $maj['major_name']);
            }

            return $response->withJson(['majors'=>$majors]);

        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }

    });

    $app->get('/', function (Request $request, Response $response, array $args) use ($container, $pdo) {

        $val = validate_request($request);
        if ($val['success']){
            return $response->withJson(['msg'=>'hello_world']);
        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }

    });

    $app->post('/student', function (Request $request, Response $response, array $args) use ($pdo) {

        $val = validate_request($request);
        if ($val['success']){
            $body = $val['body'];

            $stmt = $pdo->prepare('SELECT stuno FROM btr_student WHERE stuno = ?');
            $stmt->execute([$body['stuno']]);
            $exists = $stmt->fetchColumn();

            if ($exists){
                return $response->withStatus(409)->withJson(['msg'=>'Student already registered!']);
            }

            $stmt = $pdo->prepare('INSERT INTO btr_student(stuno, pref_name, surname, entry_year, program_length, faculty) '.
            'VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bindParam(1, $body['stuno']);
            $stmt->bindParam(2, $body['pref_name']);
            $stmt->bindParam(3, $body['surname']);
            $stmt->bindParam(4, $body['entry_year'], PDO::PARAM_INT);
            $stmt->bindParam(5, $body['program_length'], PDO::PARAM_INT);
            $stmt->bindParam(6, $body['faculty']);
            $stmt->execute();

            $stmt = $pdo->prepare('INSERT INTO btr_major (student, major_name) VALUES (?,?)');

            foreach($body['majors'] as $major){
                $stmt->execute([$body['stuno'], $major]);
            }

            return $response->withJson(['msg'=>'success']);

        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }

    });

};
