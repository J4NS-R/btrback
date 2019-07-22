<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app, PDO $pdo) {

    $app->get('/application_questions/{eventid}', function (Request $request, Response $response, array $args) use ($pdo) {

        $val = validate_request($request);
        if ($val['success']){

            $stmt = $pdo->prepare('SELECT question_text, answer_type FROM btr_question
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