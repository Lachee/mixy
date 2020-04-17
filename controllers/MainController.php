<?php namespace app\controllers;

use kiss\controllers\Controller;

class MainController extends Controller {

    
    public static function getRouting() { return "/main"; }

    function actionIndex() {
        return $this->render('index', [
            'title' => 'Truthy? '
        ]);
    }
}