<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app, PDO $pdo) {

    $app->post('/application/{stuno}/{eventid}', function (Request $request, Response $response, array $args) use ($pdo, $app) {

        $val = validate_request($request);
        if ($val['success']){

            $sub = $app->subRequest('GET', '/student_applied/'.$args['stuno'].'/'.$args['eventid'], '',
                ['Auth'=>$request->getHeader('Auth')[0]]);
            $appsub = json_decode((string) $sub->getBody(), true);

            if ($appsub['student_applied']){
                return $response->withStatus(409)->withJson(['msg'=>'Student has already applied.']);
            }

            // create application
            $stmt = $pdo->prepare('INSERT INTO btr_application (student, event) VALUES (?,?)');
            $stmt->bindParam(1, $args['stuno']);
            $stmt->bindParam(2, $args['eventid'], PDO::PARAM_INT);
            $stmt->execute();
            $appid = $pdo->lastInsertId();

            //insert answers
            $stmt = $pdo->prepare('INSERT INTO btr_app_answer (question, application, answer) VALUES (?,?,?)');

            $answers = $val['body']['answers'];
            foreach(array_keys($answers) as $qid){

                $stmt->bindParam(1, $qid, PDO::PARAM_INT);
                $stmt->bindParam(2, $appid, PDO::PARAM_INT);
                $stmt->bindParam(3, $answers[$qid]);
                $stmt->execute();

            }

            return $response->withJson(['msg'=>'success']);

        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }

    });

    $app->get('/application_questions/{eventid}', function (Request $request, Response $response, array $args) use ($pdo) {

        $val = validate_request($request);
        if ($val['success']){

            $stmt = $pdo->prepare('SELECT qid, question_text, answer_type FROM btr_question
INNER JOIN btr_event_app_questions ON qid = question
WHERE event = ?');
            $stmt->bindParam(1, $args['eventid'], PDO::PARAM_INT);
            $stmt->execute();

            $questions = array();
            while($q = $stmt->fetch()){
                array_push($questions, $q);
            }

            return $response->withJson(['questions'=>$questions]);

        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }

    });

    $app->get('/student_applied/{stuno}/{eventid}', function (Request $request, Response $response, array $args) use ($pdo) {

        $val = validate_request($request);
        if ($val['success']){

            $stmt = $pdo->prepare('SELECT stuno, pref_name, surname, entry_year, program_length, faculty 
FROM btr_student WHERE stuno = ?');
            $stmt->execute([$args['stuno']]);
            $stu = $stmt->fetch();

            if ($stu === false){
                return $response->withStatus(404)->withJson(['msg'=>'Student is not registered on the system.']);
            }

            //get majors
            $stmt = $pdo->prepare('SELECT major_name FROM btr_major WHERE student = ?');
            $stmt->execute([$stu['stuno']]);
            $stu['majors'] = array();
            while($maj = $stmt->fetch()){
                array_push($stu['majors'], $maj['major_name']);
            }

            //check application
            $stmt = $pdo->prepare('SELECT UNIX_TIMESTAMP(app_date) as unix_date FROM btr_application 
WHERE student = ? AND event = ?');
            $stmt->bindParam(1, $stu['stuno']);
            $stmt->bindParam(2, $args['eventid'], PDO::PARAM_INT);
            $stmt->execute();
            $appl = $stmt->fetch();

            if ($appl === false){
                return $response->withJson([
                    'student_applied'=> false,
                    'student_info' => $stu
                ]);
            }else{
                return $response->withJson([
                    'student_applied'=> true,
                    'student_info' => $stu,
                    'application_timestamp' => $appl['unix_date']
                ]);
            }


        }else{
            return $response->withStatus($val['code'])->withJson($val['response']);
        }


    });

};