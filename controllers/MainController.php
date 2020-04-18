<?php namespace app\controllers;

use app\components\mixer\Mixer;

use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use app\models\User;
use kiss\Kiss;

class MainController extends MixyController {
    public static function getRouting() { return "/main"; }

    function actionTest() {
        $potato = Kiss::$app->session->get('potato', 'potato');
        Kiss::$app->session->set('potato', $potato . ' x potato');
        return Response::redirect('index');
    }

    function actionIndex() {
        $val =  Kiss::$app->session->get('potato', 'no potato');
        return $this->render('index', [
            'text' => Kiss::$app->session->getJWT(),
            'value' => $val
        ]);
    }

    /** Authorizes a token */
    function actionAuth() {
        $request = HTTP::json();
        $mixerUser = Kiss::$app->mixer->requestCurrentUser($request['data']);
        if ($mixerUser === null || empty($mixerUser->email)) throw new HttpException(HTTP::BAD_REQUEST, 'invalid tokens');

        //Find a user identity with the matching email
        /** @var User */
        $user = User::findByEmail($mixerUser->email)->one();
        if ($user == null) {
            //Create a new user. Welcome
            $user = BaseObject::new(User::class, [ 'email' => $mixerUser->email ]);
            Kiss::$app->session->addNotification('Your account has been created!');
        }

        //Update exiting values
        $user->updateFromMixerUser($mixerUser);
        $user->login();
        $success = $user->save();

        //return the result of the save
        return Response::json(HTTP::OK, $success);
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