<?php namespace app\controllers;

use app\components\Mixer;
use kiss\controllers\Controller;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\Kiss;

class MainController extends MixyController {
    public static function getRouting() { return "/main"; }

    function actionLogout() {
        Kiss::$app->session->abort();
        return Response::redirect('index');
    }

    function actionIndex() {
        return $this->render('index', [
            'title' => 'Truthy? '
        ]);
    }

    /** Authorizes a token */
    function actionAuth() {
        $request = HTTP::json();
        $user = Kiss::$app->mixer->requestCurrentUser($request['data']);
        if ($user === null) throw new HttpException(HTTP::BAD_REQUEST, 'invalid tokens');

        Kiss::$app->session->set('mixer_tokens', $user->tokens);
        return Response::json(HTTP::OK, 'accepted');
    }

    function actionAuthoridddze() {
        /** @var Mixer */
        $mixer = Kiss::$app->mixer;
        $code = $mixer->shortCode();
        return Response::redirect($code->getRedirect());

        /*
        $provider = $mixer->oauthProvider;

        if (($code = HTTP::get('code', null)) !== null) {
            $state = HTTP::get('state', null);
            if ($state != Kiss::$app->session->get('_oauth2state', null))
                throw new HttpException(HTTP::BAD_REQUEST);

            $accessToken = $provider->getAccessToken('authorization_code', [ 'code' => $code ]);
            echo 'Access Token: ' . $accessToken->getToken() . "<br>";
            echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
            echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
            echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";
            exit;
        } 
        else 
        {
            $url = $provider->getAuthorizationUrl([ 'scopes' => $mixer->scopes ]);
            Kiss::$app->session->set('_oauth2state', $provider->getState());
            return Response::redirect($url);
        }
        */
    }
}