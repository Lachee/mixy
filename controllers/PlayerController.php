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

    public $identifier;
    
    public static function getRouting() { return "/player/:identifier"; }

    //[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}
    function actionIndex() {
        /** @var Screen the screen */
        $screen = null;


        if (preg_match('/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/', $this->identifier)) {
            $screen = Screen::findByUuid($this->identifier)->one(); 
        } else {
            $jwt    = Kiss::$app->jwtProvider->decode($this->identifier);
            $user   = User::findByJWT($jwt)->one();
            if ($user == null) throw new HttpException(HTTP::FORBIDDEN, 'invalid JWT token');

            /** @var Configuration */
            $configuration = Configuration::findByJWT($jwt)->one();
            if ($configuration == null) throw new HttpException(HTTP::NOT_FOUND);

            /** @var Screen the screen */
            $screen = $configuration->getScreen()->one();            
            if ($screen == null) throw new HttpException(HTTP::NOT_FOUND);

            $screen->configure($configuration);
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