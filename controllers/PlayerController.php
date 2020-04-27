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
use app\models\Configuration;

class PlayerController extends MixyController {
    
    protected $headerFile = null;
    protected $contentFile = null;
    protected $footerFile = null;

    public $uuid;
    
    public static function getRouting() { return "/player/:uuid"; }

    function actionIndex() {
        /** @var Screen the screen */
        $screen = null;

        /** @var Configuration the config */
        $configuration  = $this->getConfiguration();

        //Find the screen based on teh config link, otherwise find it by the current uuid
        if ($configuration != null) {
            $screen = $configuration->getScreen()->one();
            $screen->configure($configuration);
        } else {
            $screen = Screen::findByUuid($this->uuid)->one(); 
        }

        //404 if there is no screen matching
        if ($screen == null || !($screen instanceof Screen))
            throw new HttpException(HTTP::NOT_FOUND);

        //Return the preview
        return $this->render('index', [
            'json'  => $screen->getJsonDefaults(),
            'html'  => $screen->compileHTML(),
            'css'   => $screen->compileCSS(),
            'js'    => $screen->js,
         ]);
    }

    /** @return Configuration */
    private function getConfiguration() { 
        $query = Configuration::findByUuid($this->uuid);
        $model = $query->one();
        return $model;
    }
    
}