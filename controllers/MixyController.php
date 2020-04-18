<?php namespace app\controllers;

use kiss\controllers\Controller;
use kiss\Kiss;

class MixyController extends Controller {

    public function render($action, $options = []) {
        $mixyDefaults = [
            'clientId'  => Kiss::$app->mixer->clientId,
            'scopes'    => Kiss::$app->mixer->scopes,
        ];

        $this->registerJs("mixy.configureOAuth(" . json_encode($mixyDefaults) . ");");
        return parent::render($action, $options = []);
    }
}