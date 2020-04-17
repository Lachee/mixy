<?php namespace app\controllers;

use app\components\Mixer;
use kiss\controllers\Controller;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\Kiss;

class MixyController extends Controller {

    public function render($action, $options = []) {
        $mixyDefaults = [
            'clientId' => Kiss::$app->mixer->clientId,
            'scopes' => Kiss::$app->mixer->scopes,
        ];

        $this->registerJs("mixy.configureOAuth(" . json_encode($mixyDefaults) . ");");
        return parent::render($action, $options = []);
    }
}