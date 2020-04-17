<?php 

DEFINE('KISS_SESSIONLESS', true);
include "../../autoload.php";

use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\helpers\StringHelper;
use kiss\Kiss;
use kiss\router\RouteFactory;

//Set the default response type to JSON
Kiss::$app->setDefaultResponseType(HTTP::CONTENT_APPLICATION_JSON);

//Handle the request and respond with its payload.
$response = handle_request();
Kiss::$app->respond($response);

/** Handles the API request with a custom controller. Will return a response or payload. */
function handle_request() {

    //Prepare the route we wish to use 
    $route = $_REQUEST['route'] ?? '';
    if (empty($route) && !empty($_SERVER['REDIRECT_URL'])) {
        if (StringHelper::startsWith($_SERVER['REDIRECT_URL'], "/api"))
        {
            $route = substr($_SERVER['REDIRECT_URL'], 4);
        }
    }

    //Just exit with no response because they are accessing the API page directly
    if (empty($route)) exit;

    //Register all the routes in the specified folder
    RouteFactory::registerDirectory(Kiss::$app->baseDir() . "/controllers/api/");

    //Break up the segments and get the controller
    $segments = explode('/', $route);
    $controller = RouteFactory::route($segments);
    if ($controller == null) {
        return new HttpException(HTTP::NOT_FOUND, "endpoint does not exist");      
    }

    try {
        //Depending on the method, we want to execute specific functions
        //TODO: Catch exceptions and return them
        switch ($_SERVER['REQUEST_METHOD']) {
            default: break;

            case 'GET': 
                if (method_exists($controller, 'get'))
                    return $controller->get();
                break;      
            
            case 'DELETE': 
                if (method_exists($controller, 'delete'))
                    return $controller->delete();
                break;

            case 'PUT':
            case 'PATCH':
                if (method_exists($controller, 'put'))
                    return $controller->put(HTTP::json());
                break;

            case 'POST':
                if (method_exists($controller, 'post'))
                    return $controller->post(HTTP::json());
                break;            
        }
    }catch(\Exception $exception) {
        
        //Return any exception back as an success. The App will handle encoding this correctly.
        return $exception;
    }

    //We didn't return before, so the method is obviously not supported on this endpoint
    return new HttpException(HTTP::METHOD_NOT_ALLOWED, 'method is not supported on this endpoint');
}