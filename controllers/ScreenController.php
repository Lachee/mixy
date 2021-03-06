<?php namespace app\controllers;

use app\components\mixer\Mixer;
use app\models\Configuration;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use app\models\User;
use kiss\Kiss;
use Mixy;
use Ramsey\Uuid\Uuid;
use app\models\Screen;
use kiss\helpers\ArrayHelper;
use kiss\helpers\HTML;

class ScreenController extends MixyController {
    public $uuid;
    public static function getRouting() { return "/screen/:uuid"; }


    function actionIndex() {
        /** @var Configuration */
        $config = Configuration::findByUuid($this->uuid)->one();
        if ($config == null) throw new HttpException(HTTP::NOT_FOUND);

        /** @var Screen */
        $screen = $config->getScreen()->one();
        if ($screen == null) throw new HttpException(HTTP::BAD_REQUEST, "Configuration no longer has a valid screen");

        if (HTTP::hasPost()) {
            $config->setJson(HTTP::post('root', $screen->getJsonDefaults()));
            if ($config->save()) {
                Kiss::$app->session->addNotification('Configuration Updated', 'success');
                return Response::redirect(['index']);
            }
        }

        $screenData = $screen->getJson();
        $configData = ArrayHelper::merge($screenData, $config->getJson());
        return $this->render('index', [
            'configData'    => $configData,
            'defaultData'   => $screenData,
            'viewUrl' => Kiss::$app->baseURL() .  HTML::href([ 'player/'.  $config->jwt(Mixy::$app->getUser()) . '/'])
         ]);
    }
}