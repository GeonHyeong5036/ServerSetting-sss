<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

error_reporting(-1);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
require '../includes/DbConnect.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

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

$app->post('/createFriend', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('userId', 'friendId'), $request, $response)){
echo "string";
        $request_data = $request->getParsedBody();
        $userId = $request_data['userId'];
        $friendId = $request_data['friendId'];

        $db = new DbOperations;

        $result = $db->createUser($userId, $friendId);

        if($result == FRIEND_CREATED){
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Friend of User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

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

$app->get('/user', function(Request $request, Response $response){
    $request_data = $request->getQueryParams();
    $kakaoId = $request_data['kakaoId'];

    $db = new DbOperations;

    $user = $db->getUser($kakaoId);

    $response_data = array();

    $response_data['error'] = false;
    $response_data['users'] = $user;

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
