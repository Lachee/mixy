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

class PlayerController extends MixyController {
    
    protected $headerFile = null;
    protected $contentFile = null;
    protected $footerFile = null;

    public $uuid;
    
    public static function getRouting() { return "/player/:uuid"; }

    function actionIndex() {
        $screen = $this->getScreen();
        return $this->render('index', [
            'html' => $screen->html,
            'css' => $screen->css,
            'js' => $screen->js,
         ]);
    }
    
    /** @return Screen */
    private function getScreen() {
        $query = Screen::findByUuid($this->uuid);
        $model = $query->one();
        if ($model == null) throw new HttpException(HTTP::NOT_FOUND);
        return $model;
    }
}