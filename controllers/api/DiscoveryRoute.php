<?php namespace app\controllers\api;

use app\models\Screen;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\router\Route;
use kiss\router\RouteFactory;

class DiscoveryRoute extends Route {

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/discovery"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {
        $payload = [[ 'address' => 'wss://interactive4-dal.mixerusercontent.com/gameClient']];
        return new Response(HTTP::OK, [], $payload, HTTP::CONTENT_APPLICATION_JSON);
    }
}