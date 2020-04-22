<?php namespace app\controllers;

use app\components\mixer\Mixer;

use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use app\models\User;
use kiss\Kiss;
use Mixy;
use Ramsey\Uuid\Uuid;

class EditorController extends MixyController {
    public static function getRouting() { return "/editor"; }


    function actionIndex() {
        return $this->render('index', [ ]);
    }
}