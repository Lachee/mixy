<?php 

if (!defined("XVE_DEBUG"))
    Define("XVE_DEBUG", true);

require_once '../autoload.php';

use core\controllers\Controller;
use exception\HttpException;
use helpers\HTML;
use helpers\HTTP;
use router\RouteFactory;

//Update the base URL on the HTML
HTML::$route = HTTP::route();

//Prepare the segments
$segments = explode('/', HTML::$route);                       //Get all the segments in the route
$routable = array_slice($segments, 0, -1);              //Lob off the last segment as that is our action
$endpoint = $segments[count($segments)-1];              

//Account for the main controller
if (count($segments) <= 2) {
    $routable = [ '', 'main' ];
    $endpoint = $segments[1] ?? 'index';
}

 //If the last part is empty, then we are index
if (empty($endpoint))       
    $endpoint = 'index';       

try {
    //Register all the routes in the specified folder
    RouteFactory::registerDirectory(\App::$xve->baseDir() . "/controllers/main/", ["*.php", "**/*.php"]);

    //Get the controller
    $controller = RouteFactory::route($routable);
    if ($controller == null) {
        throw new HttpException(HTTP::NOT_FOUND, 'controller could not be found.');
    }

    if (!($controller instanceof Controller)) {
        throw new HttpException(HTTP::INTERNAL_SERVER_ERROR, 'route is not a valid Controller');
    }

    //Attempt to get the event
    return $controller->action($endpoint);
} catch(HttpException $exception) {
    return \App::$xve->respond($exception);
} catch(\Exception $exception) {
    return \App::$xve->respond(new HttpException(HTTP::INTERNAL_SERVER_ERROR, $exception));
}
