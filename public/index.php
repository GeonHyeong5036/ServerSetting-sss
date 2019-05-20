<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

error_reporting(-1);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
require '../includes/DbConnect.php';
require '../includes/DbAnalysis.php';
require '../includes/GroupDbOperations.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

/*
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "secure"=>false,
    "users" => [
        "belalkhan" => "123456",
    ]
]));
*/
/*
  endporint: createuser
  parameters: kakaoId, name, memeber
  method: Post
*/

$app->post('/createuser', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('kakaoId', 'name', 'member'), $request, $response)){

        $request_data = $request->getParsedBody();
        $kakaoId = $request_data['kakaoId'];
        $name = $request_data['name'];
        $member = $request_data['member'];

        $db = new DbOperations;

        $result = $db->createUser($kakaoId, $name, $member);

        if($result == USER_CREATED){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USER_UPDATE){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Upate';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }else if($result == USER_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        }else if($result == USER_EXISTS){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->post('/createfriend', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('userKakakoId', 'friendKakakoId'), $request, $response)){
echo "string";
        $request_data = $request->getParsedBody();
        $userKakakoId = $request_data['userKakakoId'];
        $friendKakakoId = $request_data['friendKakakoId'];

        $db = new DbOperations;

        $result = $db->createFriend($userKakakoId, $friendKakakoId);

        if($result == FRIEND_CREATED){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Friend of User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USERID_MISSING){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Not find userId in User';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }else if($result == FRIEND_SAME){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Same UserId';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }else if($result == FRIEND_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        }else if($result == FRIEND_EXISTS){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Friend of User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->post('/createtimetable', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('kakaoId', 'type', 'cellPosition'), $request, $response)){

        $request_data = $request->getParsedBody();
        $kakaoId = $request_data['kakaoId'];
        $type = $request_data['type'];
        $title = $request_data['title'];
        $place = $request_data['place'];
        $cellPosition = $request_data['cellPosition'];

        $db = new DbOperations;

        $result = $db->createTimeTable($kakaoId, $type, $title, $place, $cellPosition);

        if($result == TIMETABLE_CREATED){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'TimeTable created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USERID_MISSING){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Not find userId in User';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        }else if($result == TIMETABLE_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred in TimeTable';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);

        }else if($result == TIMETABLE_EXISTS){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'TimeTable already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->post('/createGroup', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('title', 'type'), $request, $response)){
        $kakaoIdList = $request->getQueryParams();
        $kakaoIdList = explode('[', $kakaoIdList['kakaoIdList']);
        $kakaoIdList = explode(']', $kakaoIdList[1]);
        $kakaoIdList = explode(', ', $kakaoIdList[0]);

        $request_data = $request->getParsedBody();
        $title = $request_data['title'];
        $type = $request_data['type'];

        $db = new GroupDbOperations;

        $result = $db->createGroup($kakaoIdList, $title, $type);

        if($result == GROUP_CREATED){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Group created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == GROUP_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred in Group';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }else if($result == USERANDGROUP_FAILURE){
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred in User and Group';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->put('/updatetimetable/{kakaoId}', function(Request $request, Response $response, array $args){

    $kakaoId = $args['kakaoId'];

    if(!haveEmptyParameters(array('type', 'title', 'place', 'cellPosition'), $request, $response)){

        $request_data = $request->getParsedBody();
        $type = $request_data['type'];
        $title = $request_data['title'];
        $place = $request_data['place'];
        $cellPosition = $request_data['cellPosition'];

        $db = new DbOperations;

        if($db->updateTimeTable($kakaoId, $type, $title, $place, $cellPosition)){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'TimeTable Updated Successfully';

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        }else{
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Update failed';

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        }

    }

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);

});

$app->get('/getuser', function(Request $request, Response $response){
    $request_data = $request->getQueryParams();
    $kakaoId = $request_data['kakaoId'];
    echo $kakaoId;
    $db = new DbOperations;

    $user = $db->getUser($kakaoId);

    $response_data = array();
    $response_data['error'] = false;
    $response_data['user'] = $user;

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);

});

$app->get('/gettimetables', function(Request $request, Response $response){
    $request_data = $request->getQueryParams();
    $kakaoId = $request_data['kakaoId'];

    $db = new DbOperations;

    $timeTables = $db->getTimeTables($kakaoId);

    $response_data = array();

    $response_data['error'] = false;
    $response_data['timeTables'] = $timeTables;

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);

});

$app->get('/getAvailableMeetingTimes', function(Request $request, Response $response){
    $request_data = $request->getQueryParams();
    $array = explode('[', $request_data['kakaoIds']);
    $array = explode(']', $array[1]);
    $array = explode(', ', $array[0]);

    $db = new DbAnalysis;

    $availableMeetingTimes = $db->getAvailableMeetingTimes($array);

    $response_data['error'] = false;
    $response_data['message'] = $request_data['kakaoIds'];
    $response_data['availableMeetingTimes'] = $availableMeetingTimes;
    $response_data['totalCount'] = count($availableMeetingTimes);

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);

});

$app->get('/getAsManyUserAsAvailable', function(Request $request, Response $response){
    $request_data = $request->getQueryParams();
    $array = explode('[', $request_data['kakaoIds']);
    $array = explode(']', $array[1]);
    $array = explode(', ', $array[0]);

    $db = new DbAnalysis;

    $asManyUserAsAvailableList = $db->getAsManyUserAsAvailable($array);

    $response_data['error'] = false;
    $response_data['message'] = $request_data['kakaoIds'];
    $response_data['asManyUserAsAvailableList'] = $asManyUserAsAvailableList;
    $response_data['totalCount'] = count($asManyUserAsAvailableList);

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);

});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    $db = new DbConnect;

    if($db->connect() != null){
      echo 'Connection Successfull';
    }

    return $response;
});

$app->delete('/deletetimetable/{kakaoId}/{cellPosition}', function(Request $request, Response $response, array $args){
    $kakaoId = $args['kakaoId'];
    $cellPosition = $args['cellPosition'];

    $db = new DbOperations;

    $response_data = array();

    if($db->deleteTimeTable($kakaoId, $cellPosition)){
        $response_data['error'] = false;
        $response_data['message'] = 'TimeTable has been deleted';
    }else{
        $response_data['error'] = true;
        $response_data['message'] = 'Delete failed';
    }

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true;
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

$app->run();
