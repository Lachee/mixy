<?php namespace app\controllers;

use app\components\mixer\Mixer;

use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use app\models\User;
use kiss\Kiss;
use Mixy;

class MainController extends MixyController {
    public static function getRouting() { return "/main"; }

    function actionTest() {
      
        $val =  Kiss::$app->session->get('oauth', 'no potato');

        $response = Mixy::$app->mixer->guzzle->request('GET', 'broadcasts/current', [ 
            'headers'   => [
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$val['accessToken']}",
            ]
        ]);
        $json = json_decode($response->getBody()->getContents(), true);


        return Response::json(HTTP::OK, $json);
        
        return $this->render('index', [
            'text' => Kiss::$app->session->getJWT(),
            'value' => $val['accessToken']
        ]);
    }

    function actionIndex() {
        return $this->render('index', [
            'text' => Kiss::$app->session->getJWT(),
            'value' => 'POTATE'
        ]);
    }

    /** Authorizes a token */
    function actionAuth() {
        try {
            $request = HTTP::json();
            $mixerUser = Kiss::$app->mixer->getOwner($request['data']['accessToken']);
            if ($mixerUser === null || empty($mixerUser->email)) throw new HttpException(HTTP::BAD_REQUEST, 'invalid tokens');

            //Find a user identity with the matching email
            /** @var User $user the current user*/
            $user = User::findByEmail($mixerUser->email)->one();
            if ($user == null) {
                //Create a new user. Welcome
                $user = new User([ 'email' => $mixerUser->email ]);
                Kiss::$app->session->addNotification('Your account has been created!');
            }

            //Update exiting values. We are going to save early just in case we are a new user
            $user->updateFromMixerUser($mixerUser);
            $user->setOauthTokens($request['data']);
            $user->save();

            //Login
            $success = $user->login();
            return Response::json(HTTP::OK, $success);
        }catch(\Exception $e) {
            Kiss::$app->session->addNotification($e->getMessage(), 'danger');
            return Response::json(HTTP::INTERNAL_SERVER_ERROR, $e);
        }
    }

    function actionLogout() {
        if (($user = Mixy::$app->getUser()))  $user->logout();
        return Response::redirect('/');
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