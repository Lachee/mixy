<?php
namespace controllers\main;

use App;
use controllers\ServiceController;

use exception\HttpException;
use helpers\HTTP;

class MainController extends ServiceController {

    
    public static function getRouting() { return "/main"; }

    function actionIndex() {
        return $this->render('index', [
            'title' => 'Truthy? ' . $this->service == null
        ]);
    }

    function actionException(HttpException $exception) {
        return $this->render("@/views/system/error", [ 'exception' => $exception ]);
    }

    function actionApache() {
        $error = HTTP::get('error', HTTP::I_AM_A_TEAPOT);
        return $this->actionException(new HttpException($error));
    }

    function actionTest() {
        
        //Set the default response type to JSON
        App::$xve->setDefaultResponseType(HTTP::CONTENT_APPLICATION_JSON);
        
        $astCompiler = new \xve\ast\Compiler();
        return $astCompiler->test();
    }
    

}