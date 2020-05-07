<?php namespace app\controllers\api;

use app\models\Screen;
use app\models\User;
use Exception;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\Kiss;
use kiss\router\Route;
use kiss\router\RouteFactory;
use Mixy;

class AuthenticationRefreshRoute extends Route {

    public $token;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/authentication/:token/refresh"; }

    public function post() {
        //try to parse the JWT
        $jwt = Kiss::$app->jwtProvider->decode($this->token);

        /** @var User */
        $user = User::findByJWT($jwt)->one();
        if ($user == null) throw new HttpException(HTTP::BAD_REQUEST, 'invalid user');

        //This forces the user to be refreshed, otherwise it will throw.
        $user->getMixerUser();
        return true;
    }
}