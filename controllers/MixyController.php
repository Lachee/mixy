<?php namespace app\controllers;

use Exception;
use kiss\controllers\Controller;
use kiss\exception\ExpiredOauthException;
use kiss\helpers\Response;
use kiss\Kiss;
use Mixy;

class MixyController extends Controller {

    public function action($endpoint, ...$args) {
        
        //Force a check on the mixer user, validating the oauth. We dont want to apply this rule to the /auth endpoint tho.
        if (Mixy::$app->loggedIn() && $endpoint != '/auth' && $endpoint != 'exception') {
           try { 
               Mixy::$app->getUser()->getMixerUser();
            } catch(\Exception $ex) { 
                //We failed to get the user for what ever reason, lets abort
                Mixy::$app->getUser()->logout(); 
                Mixy::$app->session->addNotification('Failed to validate the Mixer authentication.', 'danger');
                return Kiss::$app->respond(Response::redirect('/'));
            }
        }
        
        $response = parent::action($endpoint, ...$args);
        return $response;
    }

    public function render($action, $options = []) {
        $mixyDefaults = [
            'oAuth' => [
                'clientId'  => Kiss::$app->mixer->clientId,
                'scopes'    => Kiss::$app->mixer->scopes,
            ],
        ];

        $this->registerJsVariable("mixy", "new mixlib.Mixy(" . json_encode($mixyDefaults) . ")", Controller::POS_START, 'const', false);
        return parent::render($action, $options);
    }
}