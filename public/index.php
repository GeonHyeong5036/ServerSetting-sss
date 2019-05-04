<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../includes/DbOperation.php';

$app = new \Slim\App;

/*
  endporint: createuser
  parameters: imageURL, name
  method: Post
*/

$app->post('/createuser', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('imageURL', 'name'), $request, $response)){
        $request_data = $request->getParsedBody();
        $imageURL = $request_data['imageURL'];
        $name = $request_data['name'];

        $db = new DbOperations;

        $result = $db->createUser($imageURL, $name);

        if($result == USER_CREATED){

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

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

function haveEmptyParameters($required_params, $response){
    $error = false;
    $error_params = '';
    $request_params = $_REQUEST;

    foreach ($required_params as $param) {
      if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
        $error = true;
        $error_params .= $param . ', ';
      }
    }

    if($error){
      $error_detail = array();
      $error_detail['error'] = true;
      $error_detail['message'] = 'Required parameters ' . substr($error_params, 0 ,-2) . ' are missing or empty';
      $response->write(json_encode($error_detail));
    }
    return $error;
}

$app->run();

/* check connect
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    $db = new DbConnect;

    if($db->connect() != null){
      echo 'Connection Successfull';
    }

    return $response;
});
$app->run();
*/
