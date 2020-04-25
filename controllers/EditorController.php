<?php namespace app\controllers;

use app\components\mixer\Mixer;
use app\models\Screen;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use app\models\User;
use kiss\Kiss;
use Mixy;
use Ramsey\Uuid\Uuid;

class EditorController extends MixyController {

    public $uuid;

    public static function getRouting() { return "/editor/:uuid"; }

    function actionIndex() {
        $screen = $this->getScreen();
        return $this->render('index', [ 
            'fullWidth' => true,
            'screen'  => $screen
        ]);
    }
    
    /** @return Screen */
    private function getScreen() {
        $query = Screen::findByUuid($this->uuid);
        $model = $query->one();
        if ($model == null) throw new HttpException(HTTP::NOT_FOUND, 'aah');
        return $model;
    }
}