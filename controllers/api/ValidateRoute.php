<?php namespace app\controllers\api;

use app\models\Screen;
use app\models\User;
use Exception;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\Kiss;
use kiss\router\Route;
use kiss\router\RouteFactory;

class ValidateRoute extends Route {

    public $token;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/validate/:token"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {
        try {
            //try to parse the JWT
            $jwt = Kiss::$app->jwtProvider->decode($this->token);
            $user = User::findByJWT($jwt)->one();
            if ($user == null) throw new HttpException(HTTP::BAD_REQUEST, 'invalid user');
            return $jwt;
        }catch(Exception $e) {
            throw $e;
        }
    }
}